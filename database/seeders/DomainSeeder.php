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
        Domain::create(['id' => 1, 'initial' => 'COG', 'color' => '#A9A9A9', 'name' => 'Cognição']);
        Domain::create(['id' => 2, 'initial' => 'CRE', 'color' => '#9932CC', 'name' => 'Comunicação Recetiva']);
        Domain::create(['id' => 3, 'initial' => 'CEX', 'color' => '#F08080', 'name' => 'Comunicação Expressiva']);
        Domain::create(['id' => 4, 'initial' => 'COM', 'color' => '#ADD8E6', 'name' => 'Comportamento']);
        Domain::create(['id' => 5, 'initial' => 'CAC', 'color' => '#A52A2A', 'name' => 'Comportamentos de Atenção Conjunta']);
        Domain::create(['id' => 6, 'initial' => 'CSO', 'color' => '#48D1CC', 'name' => 'Competências Sociais']);
        Domain::create(['id' => 7, 'initial' => 'CSA', 'color' => '#FF6347', 'name' => 'Competências Sociais Adultos ou Pares']);
        Domain::create(['id' => 8, 'initial' => 'CSP', 'color' => '#66CDAA', 'name' => 'Competências Sociais com Pares']);
        Domain::create(['id' => 9, 'initial' => 'IMI', 'color' => '#3CB371', 'name' => 'Imitação']);
        Domain::create(['id' => 10, 'initial' => 'IPE', 'color' => '#F0E68C', 'name' => 'Independência Pessoal']);
        Domain::create(['id' => 11, 'initial' => 'IPA', 'color' => '#FFE4C4', 'name' => 'Independência Pessoal Alimentação']);
        Domain::create(['id' => 12, 'initial' => 'IPV', 'color' => '#9ACD32', 'name' => 'Independência Pessoal Vestir']);
        Domain::create(['id' => 13, 'initial' => 'IPH', 'color' => '#D8BFD8', 'name' => 'Independência Pessoal Higiene']);
        Domain::create(['id' => 14, 'initial' => 'IPT', 'color' => '#D8BFD8', 'name' => 'Independência Pessoal Tarefas']);
        Domain::create(['id' => 15, 'initial' => 'JOG', 'color' => '#E0FFFF', 'name' => 'Jogo']);
        Domain::create(['id' => 16, 'initial' => 'JOR', 'color' => '#E0FFFF', 'name' => 'Jogo de Representação']);
        Domain::create(['id' => 17, 'initial' => 'JOI', 'color' => '#FFFAFA', 'name' => 'Jogo Independente']);
        Domain::create(['id' => 18, 'initial' => 'MFI', 'color' => '#FF4500', 'name' => 'Motricidade Fina']);
        Domain::create(['id' => 19, 'initial' => 'MGR', 'color' => '#FFA500', 'name' => 'Motricidade Grossa']);
    }
}
