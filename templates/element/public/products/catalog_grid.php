<?php

$categorizedIds = [];

if ($usesCategories):
    if (($categoryLayout ?? 'normal') === 'blocks'):
        ?>
        <div class="category-blocks">
        <?php
    endif;
    foreach ($site->catalog_categories ?? [] as $category):
        $categoryProducts = [];
        foreach ($category->catalog_products ?? [] as $product) {
            if ($product->active) {
                $categoryProducts[] = $product;
                $categorizedIds[] = $product->id;
            }
        }
        if (!$categoryProducts) {
            continue;
        }
        ?>
        <section class="category" aria-labelledby="category-<?= (int)$category->id ?>">
            <h2 class="category-title" id="category-<?= (int)$category->id ?>"><?= h($category->name) ?></h2>
            <div class="products">
                <?php foreach ($categoryProducts as $product): ?>
                    <?php $renderProduct($product); ?>
                <?php endforeach; ?>
            </div>
        </section>
    <?php endforeach; ?>

    <?php
    $uncategorized = array_values(array_filter($activeProducts, static function ($product) use ($categorizedIds): bool {
        return !$product->catalog_category_id && !in_array($product->id, $categorizedIds, true);
    }));
    ?>
    <?php if ($uncategorized): ?>
        <section class="category" aria-labelledby="category-other">
            <h2 class="category-title" id="category-other"><?= h($isCatalog ? 'Otros productos' : 'Otras preparaciones') ?></h2>
            <div class="products">
                <?php foreach ($uncategorized as $product): ?>
                    <?php $renderProduct($product); ?>
                <?php endforeach; ?>
            </div>
        </section>
    <?php endif; ?>
    <?php if (($categoryLayout ?? 'normal') === 'blocks'): ?>
        </div>
    <?php endif; ?>
<?php elseif ($activeProducts): ?>
    <section class="category">
        <div class="products">
            <?php foreach ($activeProducts as $product): ?>
                <?php $renderProduct($product); ?>
            <?php endforeach; ?>
        </div>
    </section>
<?php endif; ?>
