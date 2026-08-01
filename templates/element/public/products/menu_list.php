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
        <section class="category menu-category" aria-labelledby="menu-category-<?= (int)$category->id ?>">
            <h2 class="category-title" id="menu-category-<?= (int)$category->id ?>"><?= h($category->name) ?></h2>
            <div class="menu-list">
                <?php foreach ($categoryProducts as $product): ?>
                    <?php $renderMenuItem($product); ?>
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
        <section class="category menu-category" aria-labelledby="menu-category-other">
            <h2 class="category-title" id="menu-category-other">Otras preparaciones</h2>
            <div class="menu-list">
                <?php foreach ($uncategorized as $product): ?>
                    <?php $renderMenuItem($product); ?>
                <?php endforeach; ?>
            </div>
        </section>
    <?php endif; ?>
    <?php if (($categoryLayout ?? 'normal') === 'blocks'): ?>
        </div>
    <?php endif; ?>
<?php elseif ($activeProducts): ?>
    <section class="category menu-category">
        <div class="menu-list">
            <?php foreach ($activeProducts as $product): ?>
                <?php $renderMenuItem($product); ?>
            <?php endforeach; ?>
        </div>
    </section>
<?php endif; ?>
