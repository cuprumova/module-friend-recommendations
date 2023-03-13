<?php
declare(strict_types=1);

namespace SwiftOtter\FriendRecommendations\Model\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\GraphQl\Model\Query\ContextExtensionInterface;
use Magento\GraphQl\Model\Query\ContextInterface;
use SwiftOtter\FriendRecommendations\Model\Resolver\DataProvider\CustomerRecommendationLists
    as CustomerRecommendationListsProvider;

class CustomerRecommendationLists implements ResolverInterface
{
    private CustomerRecommendationListsProvider $customerRecommendationListsProvider;

    public function __construct(
        CustomerRecommendationListsProvider $customerRecommendationListsProvider
    ) {
        $this->customerRecommendationListsProvider = $customerRecommendationListsProvider;
    }

    /**
     * {@inheritdoc}
     * @param ContextInterface @context
     * @throws GraphQlNoSuchEntityException
     */
    public function resolve(
        Field $field,
              $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        $includeProducts = $args['products'] ?? true;

        /** @var ContextInterface $context */
        if (false === $context->getExtensionAttributes()->getIsCustomer()) {
            throw new GraphQlAuthorizationException(__(
                'The current customer isn\'t authorized.'
            ));
        }
        $customerId = $context->getUserId();

        return $this->customerRecommendationListsProvider->getAllListsProUser($customerId, $includeProducts);
    }
}
