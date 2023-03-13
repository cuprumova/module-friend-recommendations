<?php
declare(strict_types=1);

namespace SwiftOtter\FriendRecommendations\Model\Resolver\DataProvider;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use SwiftOtter\FriendRecommendations\Api\RecommendationListProductRepositoryInterface;
use SwiftOtter\FriendRecommendations\Api\Data\RecommendationListInterface;
use SwiftOtter\FriendRecommendations\Api\RecommendationListRepositoryInterface;

class CustomerRecommendationLists
{
    private RecommendationListRepositoryInterface $recommendationListRepository;
    private ProductRepositoryInterface $productRepository;
    private RecommendationListProductRepositoryInterface $listProductRepository;
    private CustomerRepositoryInterface $customerRepository;
    private SearchCriteriaBuilder $searchCriteriaBuilder;

    public function __construct(
        RecommendationListRepositoryInterface $recommendationListRepository,
        ProductRepositoryInterface $productRepository,
        RecommendationListProductRepositoryInterface $listProductRepository,
        CustomerRepositoryInterface $customerRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        $this->recommendationListRepository = $recommendationListRepository;
        $this->productRepository = $productRepository;
        $this->listProductRepository = $listProductRepository;
        $this->customerRepository = $customerRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    /**
     * @param int $recommendationListId
     * @return array
     */
    private function getProductsForRecommendations(int $recommendationListId)
    {
        $this->searchCriteriaBuilder->addFilter('recommendation_list_ids', ['in' => $recommendationListId]);
        $listProducts = $this->productRepository->getList($this->searchCriteriaBuilder->create())
            ->getItems();

        $productsData = [];
        foreach ($listProducts as $product) {
            $productsData[] = $this->formatProductData($product);
        }
        return $productsData;
    }

    /**
     * @param ProductInterface $product
     * @return array
     */
    private function formatProductData($product)
    {
        return [
            'id' => (int) $product->getId(),
            'name' => $product->getName(),
            'sku' => $product->getSku(),
        ];
    }

    /**
     * @param int $userId
     * @param bool $includeProducts
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getAllListsProUser(int $userId, bool $includeProducts = false)
    {
        $customer = $this->customerRepository->getById($userId);

        $this->searchCriteriaBuilder->addFilter('email', $customer->getEmail());

        $recommendationLists = $this->recommendationListRepository
            ->getList($this->searchCriteriaBuilder->create())
            ->getItems();

        $listsData = [];

        foreach ($recommendationLists as $recommendationList) {
            $listsData[] = $this->formatListData($recommendationList, $includeProducts);
        }

        return $listsData;
    }

    /**
     * @param RecommendationListInterface $recommendationList
     * @param bool $includeProducts
     * @return array
     */
    private function formatListData(RecommendationListInterface $recommendationList, bool $includeProducts)
    {
        $products = ($includeProducts) ? $this->getProductsForRecommendations((int) $recommendationList->getId()) : [];
        return [
            'id' => (int) $recommendationList->getId(),
            'friendName' => $recommendationList->getFriendName(),
            'title' => $recommendationList->getTitle(),
            'note' => $recommendationList->getNote(),
        ] + ['products' => $products];
    }
}
