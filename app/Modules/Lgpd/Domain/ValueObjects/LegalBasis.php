<?php

namespace App\Modules\Lgpd\Domain\ValueObjects;

enum LegalBasis: string
{
    case CONSENTIMENTO = 'consentimento';
    case EXECUCAO_CONTRATO = 'execucao_contrato';
    case OBRIGACAO_LEGAL = 'obrigacao_legal';
    case TUTELA_SAUDE = 'tutela_saude';
    case LEGITIMO_INTERESSE = 'legitimo_interesse';
    case PROTECAO_VIDA = 'protecao_vida';
    case EXERCICIO_DIREITOS = 'exercicio_direitos';
    case ESTUDOS_PESQUISA = 'estudos_pesquisa';

    public function label(): string
    {
        return match ($this) {
            self::CONSENTIMENTO => 'Consentimento do titular',
            self::EXECUCAO_CONTRATO => 'Execução de contrato',
            self::OBRIGACAO_LEGAL => 'Obrigação legal ou regulatória',
            self::TUTELA_SAUDE => 'Tutela da saúde',
            self::LEGITIMO_INTERESSE => 'Legítimo interesse',
            self::PROTECAO_VIDA => 'Proteção da vida',
            self::EXERCICIO_DIREITOS => 'Exercício regular de direitos em processo',
            self::ESTUDOS_PESQUISA => 'Realização de estudos por órgão de pesquisa',
        };
    }
}
