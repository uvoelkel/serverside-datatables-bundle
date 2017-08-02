<?php

namespace Voelkel\DataTablesBundle\Tests\DataTables;

use Voelkel\DataTablesBundle\DataTables\DataToStringConverter;
use Voelkel\DataTablesBundle\DataTables\ServerSide;

class ServerSideTest extends \PHPUnit_Framework_TestCase
{
    public function testProcessRequest()
    {
        $countQuery = $this->getMockBuilder('Doctrine\ORM\AbstractQuery')
            ->disableOriginalConstructor()
            ->getMock();
        $countQuery->expects($this->exactly(1))
            ->method('getSingleScalarResult')
            ->will($this->returnValue(1));

        $paginateQuery = $this->getMockBuilder('Doctrine\ORM\AbstractQuery')
            ->disableOriginalConstructor()
            ->getMock();
        $paginateQuery->expects($this->exactly(1))
            ->method('getResult')
            ->will($this->returnValue([1]));


        $user1 = new \Voelkel\DataTablesBundle\Tests\DataTables\Entity\TestUser();
        $user1->setId(4711)
            ->setName('Testuser 1')
            ->setStatus(123);

        $group1 = new \Voelkel\DataTablesBundle\Tests\DataTables\Entity\TestGroup();
        $group1->setId(1);
        $user1->addGroup($group1);

        $group2 = new \Voelkel\DataTablesBundle\Tests\DataTables\Entity\TestGroup();
        $group2->setId(2);
        $user1->addGroup($group2);


        $entityQuery = $this->getMockBuilder('Doctrine\ORM\AbstractQuery')
            ->disableOriginalConstructor()
            ->getMock();
        $entityQuery->expects($this->exactly(1))
            ->method('getResult')
            ->will($this->returnValue([$user1]));

        $queryBuilder = $this->getMockBuilder('Doctrine\ORM\QueryBuilder')
            ->disableOriginalConstructor()
            ->disableOriginalClone()
            ->getMock();

        $queryBuilder->expects($this->exactly(1))
            ->method('leftJoin')
            ->with('u.groups', 'g_0')
            ->will($this->returnValue($queryBuilder));

        $queryBuilder->expects($this->exactly(3))
            ->method('select')
            ->withConsecutive(
                ['count(distinct(u.id))'],
                ['u'],
                ['distinct(u.id)']
            )
            ->will($this->onConsecutiveCalls(
                null,
                null,
                null
            ));
        $queryBuilder->expects($this->exactly(3))
            ->method('getQuery')
            ->will($this->onConsecutiveCalls(
                $countQuery,
                $paginateQuery,
                $entityQuery
            ));
        $queryBuilder->expects($this->exactly(1))
            ->method('setFirstResult')
            ->with(25)
            ->will($this->returnValue($queryBuilder));
        $queryBuilder->expects($this->exactly(1))
            ->method('setMaxResults')
            ->with(50)
            ->will($this->returnValue($queryBuilder));

        $queryBuilder->expects($this->exactly(1))
            ->method('andWhere')
            ->with('u.id in (:ids)')
            ->will($this->returnValue($queryBuilder));

        $repository = $this->getMockBuilder('Doctrine\ORM\EntityRepository')
            ->disableOriginalConstructor()
            ->getMock();
        $repository->expects($this->exactly(1))
            ->method('createQueryBuilder')
            ->with('u')
            ->will($this->returnValue($queryBuilder));

        /** @var \Doctrine\ORM\EntityManagerInterface|\PHPUnit_Framework_MockObject_MockObject $em */
        $em = $this->getMockBuilder('Doctrine\ORM\EntityManagerInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $em->expects($this->exactly(1))
            ->method('getRepository')
            ->with('Voelkel\DataTablesBundle\Tests\DataTables\Entity\TestUser')
            ->will($this->returnValue($repository));

        $metadata = new \Doctrine\ORM\Mapping\ClassMetadataInfo('Voelkel\DataTablesBundle\Tests\DataTables\Entity\TestUser');
        $metadata->setIdentifier(['id']);

        $em->expects($this->exactly(1))
            ->method('getClassMetadata')
            ->with('Voelkel\DataTablesBundle\Tests\DataTables\Entity\TestUser')
            ->will($this->returnValue($metadata));



        // sql mode
        $statement = $this->getMockBuilder('Doctrine\DBAL\Driver\Statement')
            ->disableOriginalConstructor()
            ->getMock();

        $statement->expects($this->exactly(1))
            ->method('fetch')
            ->will($this->returnValue(['@@sql_mode' => '']));

        $connection = $this->getMockBuilder('Doctrine\DBAL\Connection')
            ->disableOriginalConstructor()
            ->getMock();

        $connection->expects($this->exactly(1))
            ->method('executeQuery')
            ->will($this->returnValue($statement));

        $em->expects($this->exactly(1))
            ->method('getConnection')
            ->will($this->returnValue($connection));



        $table = new TestTable();
        $table->setContainer(null);

        $sfRequest = new \Symfony\Component\HttpFoundation\Request([
            'draw' => 42,
            'start' => 25,
            'length' => 50,
        ]);

        $serverSide = new ServerSide($em, new DataToStringConverter('en'));
        $respone = $serverSide->processRequest($table, $sfRequest);

        $data = json_decode($respone->getContent(), true);
        $this->assertCount(1, $data['data']);

        $row = reset($data['data']);
        $this->assertSame(4711, $row['id']);
        $this->assertSame('*Testuser 1*', $row['name_unbound']);
    }
}
