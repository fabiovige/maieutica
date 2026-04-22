<?php

namespace Database\Seeders;

use App\Models\Kid;
use App\Models\MedicalRecord;
use Illuminate\Database\Seeder;

class MedicalRecordSeeder extends Seeder
{
    public function run()
    {
        $createdBy = 987; // Professional user

        $records = [
            // Kid 1 - Antonia Silva (3 sessões)
            [
                'patient_id' => 1,
                'patient_type' => Kid::class,
                'session_date' => '2026-01-10',
                'complaint' => 'Mãe relata dificuldade de concentração em atividades escolares. Criança dispersa durante aulas e tarefas de casa.',
                'objective_technique' => 'Observação comportamental em setting clínico. Aplicação de atividades estruturadas de atenção sustentada (jogo dos 7 erros, quebra-cabeça). Escala SNAP-IV preenchida pela mãe.',
                'evolution_notes' => 'Criança apresentou boa interação inicial, porém dificuldade em manter foco por mais de 5 minutos em atividades dirigidas. Respondeu bem a reforço positivo. Necessário investigar possível TDAH. Encaminhar para avaliação neuropsicológica.',
                'referral_closure' => 'Encaminhamento para avaliação neuropsicológica com Dr. Marcos. Retorno em 15 dias.',
                'version' => 1,
                'is_current_version' => true,
                'created_by' => $createdBy,
            ],
            [
                'patient_id' => 1,
                'patient_type' => Kid::class,
                'session_date' => '2026-01-24',
                'complaint' => 'Retorno. Mãe trouxe relatório escolar indicando melhora parcial após orientações dadas na sessão anterior.',
                'objective_technique' => 'Entrevista devolutiva com a mãe. Revisão do relatório escolar. Atividades lúdicas com foco em funções executivas.',
                'evolution_notes' => 'Criança demonstrou maior engajamento nesta sessão. Conseguiu completar atividade de 10 minutos sem interrupção. Mãe aplicou técnicas de organização de rotina em casa. Aguardando resultado da avaliação neuropsicológica.',
                'referral_closure' => null,
                'version' => 1,
                'is_current_version' => true,
                'created_by' => $createdBy,
            ],
            [
                'patient_id' => 1,
                'patient_type' => Kid::class,
                'session_date' => '2026-02-07',
                'complaint' => 'Devolutiva da avaliação neuropsicológica. Resultado: perfil atencional dentro da normalidade, porém com déficit em memória operacional.',
                'objective_technique' => 'Análise conjunta do laudo neuropsicológico. Planejamento terapêutico com foco em estratégias compensatórias para memória operacional.',
                'evolution_notes' => 'Resultado descartou TDAH. Déficit em memória operacional pode explicar as dificuldades escolares. Plano: treino cognitivo semanal com exercícios de memória de trabalho, orientação à escola para adaptações pedagógicas.',
                'referral_closure' => 'Relatório enviado à escola com recomendações. Próxima sessão: iniciar treino cognitivo.',
                'version' => 1,
                'is_current_version' => true,
                'created_by' => $createdBy,
            ],

            // Kid 2 - Pedro Santos (2 sessões)
            [
                'patient_id' => 2,
                'patient_type' => Kid::class,
                'session_date' => '2026-02-12',
                'complaint' => 'Pais relatam comportamento agressivo na escola. Criança bate nos colegas quando contrariada. Suspensa 2 vezes no último mês.',
                'objective_technique' => 'Entrevista com os pais. Observação do comportamento em brincadeira livre e dirigida. Desenho da família.',
                'evolution_notes' => 'Criança demonstrou baixa tolerância à frustração durante atividades competitivas. No desenho da família, posicionou-se distante dos demais membros. Pai viaja frequentemente a trabalho. Hipótese: comportamento agressivo como resposta à ausência paterna e dificuldade de regulação emocional.',
                'referral_closure' => null,
                'version' => 1,
                'is_current_version' => true,
                'created_by' => $createdBy,
            ],
            [
                'patient_id' => 2,
                'patient_type' => Kid::class,
                'session_date' => '2026-02-26',
                'complaint' => 'Retorno. Mãe relata que criança teve dois episódios de agressividade na semana, mas conseguiu se acalmar mais rápido após intervenção da professora.',
                'objective_technique' => 'Técnicas de regulação emocional: termômetro das emoções, respiração diafragmática lúdica (soprar bolhas). Role-playing de situações de conflito.',
                'evolution_notes' => 'Pedro mostrou boa adesão às técnicas de respiração. Identificou corretamente suas emoções no termômetro (raiva, tristeza). Durante role-playing, conseguiu verbalizar alternativas ao comportamento agressivo. Orientação aos pais: manter rotina de "momento especial" com o pai via videochamada.',
                'referral_closure' => 'Sugestão de sessão familiar quando o pai retornar de viagem. Próxima sessão individual em 2 semanas.',
                'version' => 1,
                'is_current_version' => true,
                'created_by' => $createdBy,
            ],

            // Kid 6 - Helena Vieira (2 sessões)
            [
                'patient_id' => 6,
                'patient_type' => Kid::class,
                'session_date' => '2026-03-05',
                'complaint' => 'Encaminhamento escolar por dificuldade de aprendizagem em leitura e escrita. Criança no 2º ano, ainda não consegue ler palavras simples.',
                'objective_technique' => 'Avaliação de consciência fonológica (CONFIAS). Ditado de palavras e pseudopalavras. Leitura de sílabas e palavras.',
                'evolution_notes' => 'Helena apresenta déficit significativo em consciência fonológica, especialmente em nível fonêmico. Reconhece todas as letras do alfabeto, mas não consegue fazer correspondência grafema-fonema consistente. Escrita pré-silábica. Perfil compatível com hipótese de dislexia do desenvolvimento. Necessária avaliação complementar.',
                'referral_closure' => 'Encaminhamento para avaliação fonoaudiológica e oftalmológica (descartar alterações visuais). Início de intervenção fonológica na próxima sessão.',
                'version' => 1,
                'is_current_version' => true,
                'created_by' => $createdBy,
            ],
            [
                'patient_id' => 6,
                'patient_type' => Kid::class,
                'session_date' => '2026-03-19',
                'complaint' => 'Segunda sessão de intervenção. Mãe trouxe resultado oftalmológico: acuidade visual normal.',
                'objective_technique' => 'Intervenção em consciência fonológica: jogos de rimas, segmentação silábica com palmas, identificação de fonema inicial. Material concreto com letras móveis.',
                'evolution_notes' => 'Helena respondeu bem à intervenção com material concreto. Conseguiu identificar rimas em 7 de 10 pares de palavras (melhora em relação à avaliação inicial: 3/10). Segmentação silábica adequada com apoio de palmas. Ainda apresenta dificuldade em isolar fonema inicial. Aguardando avaliação fonoaudiológica.',
                'referral_closure' => null,
                'version' => 1,
                'is_current_version' => true,
                'created_by' => $createdBy,
            ],

            // Kid 7 - Lucas Lima (1 sessão)
            [
                'patient_id' => 7,
                'patient_type' => Kid::class,
                'session_date' => '2026-03-10',
                'complaint' => 'Avaliação inicial. Mãe relata que criança apresenta medo excessivo de dormir sozinha, pesadelos frequentes e choro ao ir para a escola.',
                'objective_technique' => 'Entrevista clínica com a mãe (anamnese). Observação da criança em atividade livre. Desenho livre e HTP (House-Tree-Person).',
                'evolution_notes' => 'Lucas é uma criança tímida, mas colaborativa. Manteve contato visual adequado. No desenho HTP, a casa apresenta portas e janelas fechadas, árvore com copa pequena. Figura humana com braços colados ao corpo. Mãe relata que os sintomas iniciaram após mudança de residência há 3 meses. Impressão diagnóstica: quadro ansioso reativo à mudança de ambiente. Prognóstico favorável.',
                'referral_closure' => 'Plano terapêutico: sessões semanais com foco em dessensibilização gradual e fortalecimento de recursos de enfrentamento. Orientação aos pais sobre higiene do sono.',
                'version' => 1,
                'is_current_version' => true,
                'created_by' => $createdBy,
            ],

            // Kid 8 - Valentina Lima (2 sessões)
            [
                'patient_id' => 8,
                'patient_type' => Kid::class,
                'session_date' => '2026-02-20',
                'complaint' => 'Avaliação de desenvolvimento. Pediatra encaminhou por atraso na fala. Criança de 3 anos com vocabulário restrito (aproximadamente 20 palavras).',
                'objective_technique' => 'Observação do desenvolvimento neuropsicomotor. Avaliação de linguagem receptiva e expressiva. Avaliação Multidimensional (itens de linguagem e pessoal-social). Interação com brinquedos estruturados e não estruturados.',
                'evolution_notes' => 'Valentina apresenta linguagem expressiva abaixo do esperado para a idade. Linguagem receptiva adequada (compreende comandos de 2 etapas). Usa gestos para se comunicar. Contato visual presente. Brincadeira simbólica emergente. Desenvolvimento motor adequado. Interação social adequada com adultos. Perfil sugere atraso específico de linguagem expressiva, sem indicadores de TEA.',
                'referral_closure' => 'Encaminhamento para fonoaudiologia (prioridade). Reavaliação em 3 meses. Orientações aos pais: estimulação de linguagem em contexto natural, leitura compartilhada diária.',
                'version' => 1,
                'is_current_version' => true,
                'created_by' => $createdBy,
            ],
            [
                'patient_id' => 8,
                'patient_type' => Kid::class,
                'session_date' => '2026-03-15',
                'complaint' => 'Retorno. Pais iniciaram fonoaudiologia semanal. Relatam que criança começou a usar frases de 2 palavras ("quero água", "mamãe dá").',
                'objective_technique' => 'Reavaliação de linguagem. Observação de interação com os pais. Orientação parental sobre estratégias de estimulação.',
                'evolution_notes' => 'Melhora perceptível na linguagem expressiva. Vocabulário estimado agora em 40-50 palavras. Início de combinação de 2 palavras. Pais demonstram boa adesão às orientações. Fonoaudióloga relata evolução positiva. Manter acompanhamento mensal para monitorar desenvolvimento.',
                'referral_closure' => 'Próxima reavaliação em abril. Manter fonoaudiologia semanal.',
                'version' => 1,
                'is_current_version' => true,
                'created_by' => $createdBy,
            ],

            // Kid 10 - Malu Borges (1 sessão)
            [
                'patient_id' => 10,
                'patient_type' => Kid::class,
                'session_date' => '2026-03-20',
                'complaint' => 'Primeira consulta. Escola relata que criança se isola dos colegas, não participa de atividades em grupo e chora com facilidade.',
                'objective_technique' => 'Entrevista com a mãe. Observação comportamental. Escala de habilidades sociais (SSRS - versão pais). Brincadeira livre com materiais variados.',
                'evolution_notes' => 'Malu é filha única, convive majoritariamente com adultos. Apresenta repertório de habilidades sociais restrito, especialmente em assertividade e cooperação. Em brincadeira livre, preferiu atividades solitárias (desenho, massinha). Quando convidada a interagir, aceitou após encorajamento. Não apresenta sinais de ansiedade social severa, mas sim déficit em habilidades sociais por falta de exposição.',
                'referral_closure' => 'Plano: treino de habilidades sociais em formato individual (fase 1) e posteriormente em grupo (fase 2). Orientação à escola para inclusão gradual em atividades cooperativas. Retorno em 1 semana.',
                'version' => 1,
                'is_current_version' => true,
                'created_by' => $createdBy,
            ],
        ];

        foreach ($records as $record) {
            MedicalRecord::create($record);
        }

        $this->command->info('12 prontuários criados para 6 crianças.');
    }
}
