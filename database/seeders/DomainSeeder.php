<?php

namespace Database\Seeders;

use App\Models\Domain;
use Illuminate\Database\Seeder;

class DomainSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Domain::create(['id' => 1, 'initial' => 'COG', 'name' => 'Cognição']);
        Domain::create(['id' => 2, 'initial' => 'CRE', 'name' => 'Comunicação Recetiva']);
        Domain::create(['id' => 3, 'initial' => 'CEX', 'name' => 'Comunicação Expressiva']);
        Domain::create(['id' => 4, 'initial' => 'COM', 'name' => 'Comportamento']);
        Domain::create(['id' => 5, 'initial' => 'CAC', 'name' => 'Comportamentos de Atenção Conjunta']);
        Domain::create(['id' => 6, 'initial' => 'CSO', 'name' => 'Competências Sociais']);
        Domain::create(['id' => 7, 'initial' => 'CSA', 'name' => 'Competências Sociais Adultos ou Pares']);
        Domain::create(['id' => 8, 'initial' => 'CSP', 'name' => 'Competências Sociais com Pares']);
        Domain::create(['id' => 9, 'initial' => 'IMI', 'name' => 'Imitação']);
        Domain::create(['id' => 10, 'initial' => 'IPE', 'name' => 'Independência Pessoal']);
        Domain::create(['id' => 11, 'initial' => 'IPA', 'name' => 'Independência Pessoal Alimentação']);
        Domain::create(['id' => 12, 'initial' => 'IPV', 'name' => 'Independência Pessoal Vestir']);
        Domain::create(['id' => 13, 'initial' => 'IPH', 'name' => 'Independência Pessoal Higiene']);
        Domain::create(['id' => 14, 'initial' => 'IPT', 'name' => 'Independência Pessoal Tarefas']);
        Domain::create(['id' => 15, 'initial' => 'JOG', 'name' => 'Jogo']);
        Domain::create(['id' => 16, 'initial' => 'JOR', 'name' => 'Jogo de Representação']);
        Domain::create(['id' => 17, 'initial' => 'JOI', 'name' => 'Jogo Independente']);
        Domain::create(['id' => 18, 'initial' => 'MFI', 'name' => 'Motricidade Fina']);
        Domain::create(['id' => 19, 'initial' => 'MGR', 'name' => 'Motricidade Grossa']);
    }
}
