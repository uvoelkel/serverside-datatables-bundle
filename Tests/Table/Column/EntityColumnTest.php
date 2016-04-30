<?php

namespace Voelkel\DataTablesBundle\Tests\Table\Column;

use Voelkel\DataTablesBundle\Table\Column\EntityColumn;

class EntityColumnTest extends \PHPUnit_Framework_TestCase
{
    public function testGetEntityField()
    {
        $column = new EntityColumn('test_name', 'testAssociation', 'testField');
        $this->assertEquals('testField', $column->getEntityField());
    }

    public function testGetEntityPrefix()
    {
        $column = new EntityColumn('test_name', 'testAssociationCamelCase', 'testField');
        $this->assertEquals('tacc_0', $column->getEntityPrefix());

        $column = new EntityColumn('test_name', 'test_association_snake_case', 'testField');
        $this->assertEquals('tasc_0', $column->getEntityPrefix());

        $column = new EntityColumn('test_name', 'testAaBbCcDdEeFfGgHhIiJjKkLlMmNnOoPpQqRrSsTtUuVvWwXxYyZz', 'testField');
        $this->assertEquals('tabcdefghijklmnopqrstuvwxyz_0', $column->getEntityPrefix());

        $column = new EntityColumn('test_name', 'test_aa_bb_cc_dd_ee_ff_gg_hh_ii_jj_kk_ll_mm_nn_oo_pp_qq_rr_ss_tt_uu_vv_ww_xx_yy_zz', 'testField');
        $this->assertEquals('tabcdefghijklmnopqrstuvwxyz_1', $column->getEntityPrefix());
    }
}
