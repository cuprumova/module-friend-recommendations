<?php
declare(strict_types=1);

namespace SwiftOtter\FriendRecommendations\Model\Resolver\Service;

use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use SwiftOtter\FriendRecommendations\Api\Data\RecommendationListInterface;
use SwiftOtter\FriendRecommendations\Api\Data\RecommendationListInterfaceFactory;
use SwiftOtter\FriendRecommendations\Api\Data\RecommendationListProductInterfaceFactory;
use SwiftOtter\FriendRecommendations\Api\RecommendationListProductRepositoryInterface;
use SwiftOtter\FriendRecommendations\Api\RecommendationListRepositoryInterface;

class CreateRecommendationList
{
    private RecommendationListRepositoryInterface $recommendationListRepository;
    private SearchCriteriaBuilder $searchCriteriaBuilder;
    private RecommendationListProductRepositoryInterface $recommendationListProductRepository;
    private RecommendationListInterfaceFactory $recommendationListFactory;
    private RecommendationListProductInterfaceFactory $recommendationListProductFactory;

    public function __construct(
        RecommendationListRepositoryInterface $recommendationListRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        RecommendationListProductRepositoryInterface $recommendationListProductRepository,
        RecommendationListInterfaceFactory $recommendationListFactory,
        RecommendationListProductInterfaceFactory $recommendationListProductFactory
    ) {
        $this->recommendationListRepository = $recommendationListRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->recommendationListProductRepository = $recommendationListProductRepository;
        $this->recommendationListFactory = $recommendationListFactory;
        $this->recommendationListProductFactory = $recommendationListProductFactory;
    }

    /**
     * @param array $args
     * @return array
     * @throws CouldNotSaveException
     */
    public function execute($args): array
    {
        $recommendationList = $this->recommendationListFactory->create();
        $recommendationList->setEmail($args['email'])
            ->setFriendName($args['friendName'])
            ->setTitle($args['title'])
            ->setNote($args['note']);

        $savedRecommendationList = $this->recommendationListRepository->save($recommendationList);

        $productSkus = $args['productSkus'];
        $this->saveRecommendationListProducts((int) $savedRecommendationList->getId(), $productSkus);

        return $this->formatResponseData(
            $savedRecommendationList
        );
    }

    /**
     * @param int $listId
     * @param array $productSkus
     * @return array[]
     * @throws CouldNotSaveException
     */
    private function saveRecommendationListProducts(int $listId, array $productSkus)
    {
        $savedRecommendationListProduct = [];
        foreach ($productSkus as $productSku) {
            $recommendationListProduct = $this->recommendationListProductFactory->create();
            $recommendationListProduct->setListId($listId)
                ->setSku($productSku);

            $savedRecommendationListProduct[] = $this->recommendationListProductRepository->save(
                $recommendationListProduct
            );
        }

        return ['products' => $savedRecommendationListProduct];
    }

    /**
     * @param RecommendationListInterface $recommendationList
     * @return array
     */
    private function formatResponseData(
        RecommendationListInterface $recommendationList
    ): array {

        return [
            'email' => $recommendationList->getEmail(),
            'friendName' => $recommendationList->getFriendName(),
            'title' => $recommendationList->getTitle(),
            'note' => $recommendationList->getNote(),
        ];
    }

}
