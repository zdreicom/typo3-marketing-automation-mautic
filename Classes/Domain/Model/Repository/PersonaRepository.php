<?php
declare(strict_types = 1);
namespace Bitmotion\MarketingAutomationMautic\Domain\Model\Repository;

use Doctrine\DBAL\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class PersonaRepository
{
    public function findBySegments(array $segments): array
    {
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('tx_marketingautomation_persona');
        $expressionBuilder = $queryBuilder->expr();
        $persona = $queryBuilder->select('*')
            ->from('tx_marketingautomation_persona')
            ->where(
                $expressionBuilder->in(
                    'uid',
                    $queryBuilder->createNamedParameter($segments, Connection::PARAM_INT_ARRAY)
                )
            )
            ->orderBy('sorting')
            ->setMaxResults(1)
            ->execute()
            ->fetchAll();

        return $persona[0] ?? [];
    }
}
