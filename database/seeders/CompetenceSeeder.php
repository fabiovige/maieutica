<?php

namespace Database\Seeders;

use App\Models\Competence;
use App\Models\Level;
use Illuminate\Database\Seeder;

class CompetenceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Competence::create(['id' => 1, 'initial' => 'COG', 'name' => 'Cognição']);
        Competence::create(['id' => 2, 'initial' => 'CRE', 'name' => 'Comunicação Recetiva']);
        Competence::create(['id' => 3, 'initial' => 'CEX', 'name' => 'Comunicação Expressiva']);
        Competence::create(['id' => 4, 'initial' => 'COM', 'name' => 'Comportamento']);
        Competence::create(['id' => 5, 'initial' => 'CAC', 'name' => 'Comportamentos de Atenção Conjunta']);
        Competence::create(['id' => 6, 'initial' => 'CSO', 'name' => 'Competências Sociais']);
        Competence::create(['id' => 7, 'initial' => 'CSA', 'name' => 'Competências Sociais Adultos ou Pares']);
        Competence::create(['id' => 8, 'initial' => 'CSP', 'name' => 'Competências Sociais com Pares']);
        Competence::create(['id' => 9, 'initial' => 'IMI', 'name' => 'Imitação']);
        Competence::create(['id' => 10, 'initial' => 'IPE', 'name' => 'Independência Pessoal']);
        Competence::create(['id' => 11, 'initial' => 'IPA', 'name' => 'Independência Pessoal Alimentação']);
        Competence::create(['id' => 12, 'initial' => 'IPV', 'name' => 'Independência Pessoal Vestir']);
        Competence::create(['id' => 13, 'initial' => 'IPH', 'name' => 'Independência Pessoal Higiene']);
        Competence::create(['id' => 14, 'initial' => 'IPT', 'name' => 'Independência Pessoal Tarefas']);
        Competence::create(['id' => 15, 'initial' => 'JOG', 'name' => 'Jogo']);
        Competence::create(['id' => 16, 'initial' => 'JOR', 'name' => 'Jogo de Representação']);
        Competence::create(['id' => 17, 'initial' => 'JOI', 'name' => 'Jogo Independente']);
        Competence::create(['id' => 18, 'initial' => 'MFI', 'name' => 'Motricidade Fina']);
        Competence::create(['id' => 19, 'initial' => 'MGR', 'name' => 'Motricidade Grossa']);
    }
}
