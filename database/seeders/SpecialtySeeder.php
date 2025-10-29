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
                'name' => 'Pediatria',
                'description' => 'Especialidade médica dedicada aos cuidados da criança e do adolescente',
                'created_by' => 1
            ],
            [
                'name' => 'Psicologia',
                'description' => 'Profissional que estuda o comportamento e processos mentais',
                'created_by' => 1
            ],
            [
                'name' => 'Fonoaudiologia',
                'description' => 'Profissional que trata distúrbios da comunicação',
                'created_by' => 1
            ],
            [
                'name' => 'Terapia Ocupacional',
                'description' => 'Profissional que promove a reabilitação física e mental',
                'created_by' => 1
            ],
            [
                'name' => 'Neurologia Infantil',
                'description' => 'Especialidade que estuda e trata distúrbios do sistema nervoso em crianças',
                'created_by' => 1
            ],
            [
                'name' => 'Fisioterapia',
                'description' => 'Profissional que atua na prevenção e reabilitação de distúrbios motores e funcionais',
                'created_by' => 1
            ],
            [
                'name' => 'Psicopedagogia',
                'description' => 'Profissional que auxilia no processo de aprendizagem e dificuldades escolares',
                'created_by' => 1
            ],
            [
                'name' => 'Nutrição Infantil',
                'description' => 'Profissional que orienta sobre alimentação adequada e saudável para crianças',
                'created_by' => 1
            ],
            [
                'name' => 'Psiquiatria Infantil',
                'description' => 'Especialista em saúde mental de crianças e adolescentes',
                'created_by' => 1
            ],
            [
                'name' => 'Enfermagem Pediátrica',
                'description' => 'Profissional que cuida da assistência e bem-estar da criança em ambiente clínico',
                'created_by' => 1
            ],
            [
                'name' => 'Educação Física Infantil',
                'description' => 'Profissional que estimula o desenvolvimento motor e hábitos saudáveis nas crianças',
                'created_by' => 1
            ],
            [
                'name' => 'Psicomotricidade',
                'description' => 'Profissional que trabalha a integração entre corpo, movimento e mente no desenvolvimento infantil',
                'created_by' => 1
            ],
            [
                'name' => 'Musicoterapia',
                'description' => 'Profissional que utiliza a música como ferramenta terapêutica para desenvolvimento emocional e cognitivo',
                'created_by' => 1
            ],
            [
                'name' => 'Assistência Social',
                'description' => 'Profissional que apoia famílias e crianças em vulnerabilidade social',
                'created_by' => 1
            ]
        ];


        foreach ($specialties as $specialty) {
            Specialty::firstOrCreate(
                ['name' => $specialty['name']],
                $specialty
            );
        }
    }
}
