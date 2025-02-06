<?php

namespace Database\Seeders;

use App\Models\Specialty;
use Illuminate\Database\Seeder;

class SpecialtySeeder extends Seeder
{
    public function run()
    {
        $specialties = [
            [
                'name' => 'Neuropsicologia',
                'description' => 'Estudo da relação entre o cérebro e o comportamento'
            ],
            [
                'name' => 'Psicologia Cognitiva',
                'description' => 'Estudo dos processos mentais por trás do comportamento'
            ],
            [
                'name' => 'Psicopedagogia',
                'description' => 'Estudo dos processos de aprendizagem e suas dificuldades'
            ],
            [
                'name' => 'Fonoaudiologia',
                'description' => 'Especialidade voltada para comunicação, fala e linguagem'
            ],
            [
                'name' => 'Terapia Ocupacional',
                'description' => 'Reabilitação das funções práticas e cotidianas'
            ],
            [
                'name' => 'Neurologia Pediátrica',
                'description' => 'Especialidade médica focada em distúrbios neurológicos infantis'
            ],
            [
                'name' => 'Psiquiatria Infantil',
                'description' => 'Especialidade médica focada em saúde mental infantil'
            ],
            [
                'name' => 'Neuropediatria',
                'description' => 'Especialidade médica que trata doenças neurológicas em crianças'
            ],
            [
                'name' => 'Psicomotricidade',
                'description' => 'Desenvolvimento motor e sua relação com aspectos cognitivos'
            ],
            [
                'name' => 'Neuropsicopedagogia',
                'description' => 'Interface entre neurociência e educação'
            ],
            [
                'name' => 'Psicologia do Desenvolvimento',
                'description' => 'Estudo do desenvolvimento humano ao longo da vida'
            ],
            [
                'name' => 'Reabilitação Cognitiva',
                'description' => 'Técnicas para melhorar funções cognitivas'
            ],
            [
                'name' => 'Estimulação Precoce',
                'description' => 'Intervenção em bebês e crianças pequenas'
            ],
            [
                'name' => 'Psicologia Educacional',
                'description' => 'Interface entre psicologia e processos educacionais'
            ],
            [
                'name' => 'Neurologia Comportamental',
                'description' => 'Estudo da base neural do comportamento'
            ],
            [
                'name' => 'Terapia ABA',
                'description' => 'Análise do Comportamento Aplicada'
            ],
            [
                'name' => 'Integração Sensorial',
                'description' => 'Abordagem terapêutica focada no processamento sensorial'
            ],
            [
                'name' => 'Musicoterapia',
                'description' => 'Uso terapêutico da música no desenvolvimento'
            ],
            [
                'name' => 'Arteterapia',
                'description' => 'Uso terapêutico da arte no desenvolvimento'
            ],
            [
                'name' => 'Psicologia Comportamental',
                'description' => 'Abordagem focada na modificação do comportamento'
            ]
        ];

        foreach ($specialties as $specialty) {
            Specialty::firstOrCreate(
                ['name' => $specialty['name']],
                [
                    'description' => $specialty['description'],
                    'created_by' => 1
                ]
            );
        }
    }
}
