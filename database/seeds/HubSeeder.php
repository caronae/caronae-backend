<?php

use Caronae\Models\Hub;
use Illuminate\Database\Seeder;

class HubSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
    	DatabaseSeeder::emptyTable('hubs');

    	collect([
            ['campus' => 'Cidade Universitária', 'center' => 'CCMN', 'name' => 'CCMN: Frente'],
            ['campus' => 'Cidade Universitária', 'center' => 'CCMN', 'name' => 'CCMN: Fundos'],
            ['campus' => 'Cidade Universitária', 'center' => 'CCS', 'name' => 'CCS: Frente'],
            ['campus' => 'Cidade Universitária', 'center' => 'CCS', 'name' => 'CCS: Fundos'],
            ['campus' => 'Cidade Universitária', 'center' => 'CCS', 'name' => 'CCS: HUCFF'],
            ['campus' => 'Cidade Universitária', 'center' => 'CT', 'name' => 'CT: Bloco A'],
            ['campus' => 'Cidade Universitária', 'center' => 'CT', 'name' => 'CT: Bloco D'],
            ['campus' => 'Cidade Universitária', 'center' => 'CT', 'name' => 'CT: Bloco H'],
            ['campus' => 'Cidade Universitária', 'center' => 'Letras', 'name' => 'Letras'],
            ['campus' => 'Cidade Universitária', 'center' => 'Reitoria', 'name' => 'Reitoria'],
            ['campus' => 'Cidade Universitária', 'center' => 'EEFD', 'name' => 'EEFD'],
        ])->each(function($data){
            Hub::create($data);
        });
    }
}
