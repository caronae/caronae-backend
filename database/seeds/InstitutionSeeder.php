<?php

use Caronae\Models\Campus;
use Caronae\Models\Hub;
use Caronae\Models\Institution;
use Illuminate\Database\Seeder;

class InstitutionSeeder extends Seeder
{
    public function run()
    {
        DatabaseSeeder::emptyTable('institutions');
        DatabaseSeeder::emptyTable('hubs');

        $ufrj = Institution::create([
            'name' => 'UFRJ',
            'slug' => 'ufrj',
            'authentication_url' => 'http://cas.ufrj.br/example',
            'going_label' => 'Chegando na UFRJ',
            'leaving_label' => 'Saindo da UFRJ',
        ]);

        $fundao = Campus::create(['name' => 'Cidade Universitária', 'institution_id' => $ufrj->id]);
        collect([
            ['center' => 'CCMN', 'name' => 'CCMN: Frente'],
            ['center' => 'CCMN', 'name' => 'CCMN: Fundos'],
            ['center' => 'CCS', 'name' => 'CCS: Frente'],
            ['center' => 'CCS', 'name' => 'CCS: Fundos'],
            ['center' => 'CCS', 'name' => 'CCS: HUCFF'],
            ['center' => 'CT', 'name' => 'CT: Bloco A'],
            ['center' => 'CT', 'name' => 'CT: Bloco D'],
            ['center' => 'CT', 'name' => 'CT: Bloco H'],
            ['center' => 'Letras', 'name' => 'Letras'],
            ['center' => 'Reitoria', 'name' => 'Reitoria'],
            ['center' => 'EEFD', 'name' => 'EEFD'],
        ])->each(function ($data) use ($fundao) {
            $data['campus_id'] = $fundao->id;
            Hub::create($data);
        });

        $pv = Campus::create(['name' => 'Praia Vermelha', 'institution_id' => $ufrj->id]);
        collect([
            ['center' => 'PV', 'name' => 'Praia Vermelha: Psicologia'],
            ['center' => 'PV', 'name' => 'Praia Vermelha: Pinel-Fundos'],
        ])->each(function ($data) use ($pv) {
            $data['campus_id'] = $pv->id;
            Hub::create($data);
        });

        echo "Created institutions.\n";
    }
}
