<?php
declare(strict_types=1);

namespace SwiftOtter\FriendRecommendations\Model\Resolver;

use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\GraphQl\Model\Query\ContextInterface;
use SwiftOtter\FriendRecommendations\Model\Resolver\Service\CreateRecommendationList
    as CreateRecommendationListService;

class CreateRecommendationList implements ResolverInterface
{
    private CreateRecommendationListService $createRecommendationListService;

    public function __construct(
        CreateRecommendationListService $createRecommendationListService
    ) {
        $this->createRecommendationListService = $createRecommendationListService;
    }

    /**
     * {@inheritdoc}
     * @param ContextInterface $context
     * @throws GraphQlInputException
     * @throws GraphQlNoSuchEntityException
     * @throws CouldNotSaveException
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {

        /** @var ContextInterface $context */
        if (false === $context->getExtensionAttributes()->getIsCustomer()) {
            throw new GraphQlAuthorizationException(__(
                'The current customer isn\'t authorized.'
            ));
        }

        if (empty($args['email'])) {
            throw new GraphQlInputException(__('Email is required to register a recommendation list'));
        }

        if (empty($args['friendName'])) {
            throw new GraphQlInputException(__('Friend name is required to register a recommendation list'));
        }

        if (empty($args['productSkus'])) {
            throw new GraphQlInputException(
                __('Product sku(-s) is/are required to register a recommendation list')
            );
        }

        return $this->createRecommendationListService->execute($args);
    }
}
