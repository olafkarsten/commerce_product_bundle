<?php

namespace Drupal\commerce_product_bundle\Plugin\Field\FieldFormatter;

use Drupal\commerce\Context;
use Drupal\commerce_price\Plugin\Field\FieldFormatter\PriceCalculatedFormatter;
use Drupal\commerce_product_bundle\Entity\BundleInterface;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Language\LanguageInterface;

/**
 * Plugin implementation of the 'commerce_price_calculated' formatter.
 *
 * @FieldFormatter(
 *   id = "commerce_product_bundle_calculated",
 *   label = @Translation("Calculated product bundle price"),
 *   field_types = {
 *     "commerce_price"
 *   }
 * )
 */
class BundlePriceCalculatedFormatter extends PriceCalculatedFormatter {

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];

    /** @var \Drupal\commerce\PurchasableEntityInterface $purchasable_entity */
    $purchasable_entity = $items->getEntity();
    // We only handle product bundles. Let the commerce core calculated price
    // formatter handle other purchasable entities.
    if (!$purchasable_entity->getEntityType()->entityClassImplements(BundleInterface::class)) {
      return parent::viewElements($items, $langcode);
    }

    $context = new Context($this->currentUser, $this->currentStore->getStore(), NULL, [
      'field_name' => $items->getName(),
    ]);

    // We need to run the logic, even if we have no valid price from the bundle
    // entity itself. The bundle price resolver will calculate a price from the
    // product bundle items.
    for ($delta = $items->isEmpty() ? 0 : 1; $delta <= count($items); $delta = $delta + 1) {
      $resolved_price = $this->chainPriceResolver->resolve($purchasable_entity, 1, $context);
      if ($resolved_price) {
        $number = $resolved_price->getNumber();
        $currency_code = $resolved_price->getCurrencyCode();
        $options = $this->getFormattingOptions();
        $elements[$delta] = [
          '#markup' => $this->currencyFormatter->format($number, $currency_code, $options),
          '#cache' => [
            'tags' => $purchasable_entity->getCacheTags(),
            'contexts' => Cache::mergeContexts($purchasable_entity->getCacheContexts(), [
              'languages:' . LanguageInterface::TYPE_INTERFACE,
              'country',
            ]),
          ],
        ];
      }
    }

    return $elements;
  }

}
