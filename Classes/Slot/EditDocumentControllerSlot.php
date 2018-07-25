<?php
declare(strict_types = 1);
namespace Bitmotion\MarketingAutomationMautic\Slot;

use Bitmotion\MarketingAutomationMautic\Domain\Model\Repository\SegmentRepository;
use TYPO3\CMS\Backend\Controller\EditDocumentController;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class EditDocumentControllerSlot
{
    /**
     * @var SegmentRepository
     */
    protected $segmentRepository;

    public function __construct(SegmentRepository $segmentRepository)
    {
        $this->segmentRepository = $segmentRepository;
    }

    public function synchronizeSegments(EditDocumentController $editDocumentController)
    {
        if (empty(GeneralUtility::_GP('tx_marketingautomation_segments')['updateSegments'])
            || empty($editDocumentController->editconf['tx_marketingautomation_persona'])
        ) {
            return;
        }

        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getQueryBuilderForTable('tx_marketingautomation_segment');
        $queryBuilder->getRestrictions()->removeAll();

        $result = $queryBuilder->select('*')
            ->from('tx_marketingautomation_segment')
            ->execute();

        $availableSegments = [];
        while ($row = $result->fetch()) {
            $availableSegments[$row['uid']] = $row;
        }
        $result->closeCursor();

        $queryBuilder->update('tx_marketingautomation_segment')
            ->set('deleted', 1)
            ->execute();

        $segments = $this->segmentRepository->findAll();
        foreach ($segments as $segment) {
            $dateAdded = \DateTime::createFromFormat('Y-m-d\TH:i:sP', $segment['dateAdded']);
            if (!empty($segment['dateModified'])) {
                $dateModified = \DateTime::createFromFormat('Y-m-d\TH:i:sP', $segment['dateModified']);
            } else {
                $dateModified = \DateTime::createFromFormat('U', (string)$GLOBALS['EXEC_TIME']);
            }

            if (!isset($availableSegments[$segment['id']])) {
                $insertQueryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
                    ->getQueryBuilderForTable('tx_marketingautomation_segment');
                $insertQueryBuilder->insert('tx_marketingautomation_segment')
                    ->values(
                        [
                            'uid' => (int)$segment['id'],
                            'crdate' => $dateAdded->getTimestamp(),
                            'tstamp' => $dateModified->getTimestamp(),
                            'deleted' => (int)!$segment['isPublished'],
                            'title' => $segment['name'],
                        ]
                    )
                    ->execute();
            } else {
                $updateQueryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
                    ->getQueryBuilderForTable('tx_marketingautomation_segment');
                $updateQueryBuilder->update('tx_marketingautomation_segment')
                    ->where(
                        $updateQueryBuilder->expr()->eq(
                            'uid',
                            $updateQueryBuilder->createNamedParameter($segment['id'], \PDO::PARAM_INT)
                        )
                    )
                    ->set('crdate', $dateAdded->getTimestamp())
                    ->set('tstamp', $dateModified->getTimestamp())
                    ->set('deleted', (int)!$segment['isPublished'])
                    ->set('title', $segment['name'])
                    ->execute();
            }
        }
    }
}
