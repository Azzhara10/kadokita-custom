
<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

get_header();

$categories = array(
    'accessories'     => 'Accessories',
    'cute-collectibles' => 'Cute Collectibles',
    'fashion'         => 'Fashion',
    'greeting-cards'  => 'Greeting Cards',
    'lifestyle'       => 'Lifestyle',
    'polaroid-photos' => 'Polaroid Photos',
    'self-care'       => 'Self Care',
    'snacks'          => 'Snacks',
    'stationery'      => 'Stationery',
);

$packaging_products = wc_get_products(
    array(
        'status'   => 'publish',
        'limit'    => -1,
        'category' => array( 'packaging' ),
        'orderby'  => 'menu_order title',
        'order'    => 'ASC',
    )
);
?>

<main class="kk-custom-page">

    <section class="kk-custom-hero">
        <p class="kk-custom-eyebrow">YOUR GIFT, YOUR WAY</p>
        <h1>Buat Custom Hampers</h1>
        <p>
            Pilih hadiah favoritmu, tentukan kemasannya,
            dan buat hampers spesial sesuai keinginanmu.
        </p>
    </section>

    <div class="kk-custom-container">


    <div class="kk-custom-layout">
    <div class="kk-custom-main">
    <!-- Pilihan Budget Hampers -->
    <div class="kk-budget-section">
        <label for="kk-budget">Tentukan Budget Hampers</label>

        <select id="kk-budget" name="kadokita_budget" required>
            <option value="">-- Pilih budget --</option>
            <option value="50000">Rp50.000</option>
            <option value="100000">Rp100.000</option>
            <option value="150000">Rp150.000</option>
            <option value="200000">Rp200.000</option>
            <option value="300000">Rp300.000</option>
            <option value="500000">Rp500.000</option>
        </select>

        <p id="kk-budget-status">
            Pilih budget untuk mulai merancang hampers.
        </p>
    </div>

    <!-- Kode HTML halaman yang sudah ada tetap dilanjutkan di sini -->


        <?php if ( isset( $_GET['custom_hampers_error'] ) ) : ?>
            <div class="kk-custom-notice" role="alert">
                <?php
                $error = sanitize_key(
                    wp_unslash( $_GET['custom_hampers_error'] )
                );

                if ( 'empty' === $error ) {
                    echo 'Pilih minimal satu produk sebelum melanjutkan.';
                } elseif ( 'packaging' === $error ) {
                    echo 'Pilih kemasan yang tersedia.';
                } elseif ( 'product' === $error ) {
                    echo 'Ada produk yang tidak tersedia atau kategorinya tidak sesuai. Silakan pilih kembali.';
                } else {
                    echo 'Pilihan belum berhasil ditambahkan. Silakan coba lagi.';
                }
                ?>
            </div>
        <?php endif; ?>

        <form method="post"
              action="<?php echo esc_url( get_permalink() ); ?>"
              id="kk-custom-form">

            <?php
            wp_nonce_field(
                'kadokita_custom_hampers',
                'kadokita_custom_hampers_nonce'
            );
            ?>

            <input type="hidden"
                   name="kadokita_custom_hampers_submit"
                   value="1">

            <section class="kk-custom-section">
                <div class="kk-custom-heading">
                    <span>01</span>
                    <div>
                        <h2>Pilih Isi Hampers</h2>
                        <p>Kamu bisa memilih lebih dari satu produk.</p>
                    </div>
                </div>

                <?php foreach ( $categories as $slug => $category_name ) : ?>

                    <?php
                    $products = wc_get_products(
                        array(
                            'status'   => 'publish',
                            'limit'    => -1,
                            'category' => array( $slug ),
                            'orderby'  => 'menu_order title',
                            'order'    => 'ASC',
                        )
                    );
                    ?>

                    <div class="kk-product-category">
                        <h3><?php echo esc_html( $category_name ); ?></h3>

                        <?php if ( empty( $products ) ) : ?>

                            <p class="kk-empty-category">
                                Belum ada produk di kategori ini.
                            </p>

                        <?php else : ?>

                            <div class="kk-product-grid">

                                <?php foreach ( $products as $product ) : ?>

                                    <?php
                                    if (
                                        ! $product->is_visible() ||
                                        ! $product->is_purchasable() ||
                                        ! $product->is_in_stock()
                                    ) {
                                        continue;
                                    }

                                    // Untuk formulir ini, gunakan produk sederhana.
                                    if ( ! $product->is_type( 'simple' ) ) {
                                        continue;
                                    }

                                    $price = (float) wc_get_price_to_display(
                                        $product
                                    );
                                    ?>

                                    <label class="kk-product-card">
                                        <input
                                            type="checkbox"
                                            class="kk-product-checkbox"
                                            name="kadokita_products[]"
                                            value="<?php echo esc_attr( $product->get_id() ); ?>"
                                            data-price="<?php echo esc_attr( $price ); ?>"
                                        >

                                        <div class="kk-product-image">
                                            <?php
                                            echo wp_kses_post(
                                                $product->get_image(
                                                    'woocommerce_thumbnail'
                                                )
                                            );
                                            ?>
                                        </div>

                                        <div class="kk-product-info">
                                            <h4>
                                                <?php echo esc_html( $product->get_name() ); ?>
                                            </h4>

                                            <span class="kk-product-price">
                                                <?php echo wp_kses_post( wc_price( $price ) ); ?>
                                            </span>

                                            <?php if ( $product->get_short_description() ) : ?>
    <p>
        <?php
        echo wp_kses_post(
            $product->get_short_description()
        );
        ?>
    </p>
<?php endif; ?>
                                        </div>

                                        
<div class="kk-quantity-control" onclick="event.stopPropagation();">
    <button type="button" class="kk-quantity-minus"
        aria-label="Kurangi jumlah" disabled>−</button>

    <input
        type="number"
        class="kk-product-quantity"
        name="kadokita_quantities[<?php echo esc_attr( $product->get_id() ); ?>]"
        value="1"
        min="1"
        max="99"
        step="1"
        aria-label="Jumlah produk"
        disabled
    >

    <button type="button" class="kk-quantity-plus"
        aria-label="Tambah jumlah" disabled>+</button>
</div>

<span class="kk-select-indicator">Pilih</span>

                                    </label>

                                <?php endforeach; ?>

                            </div>

                        <?php endif; ?>
                    </div>

                <?php endforeach; ?>

            </section>

            <section class="kk-custom-section">
                <div class="kk-custom-heading">
                    <span>02</span>
                    <div>
                        <h2>Pilih Kemasan</h2>
                        <p>Pilih satu kemasan untuk semua isi hampersmu.</p>
                    </div>
                </div>

                <?php if ( empty( $packaging_products ) ) : ?>

                    <p class="kk-empty-category">
                        Produk kemasan belum ditemukan. Pastikan Hamper Box
                        dan Paper Bag berada di kategori Packaging.
                    </p>

                <?php else : ?>

                    <div class="kk-packaging-grid">

                        <?php foreach ( $packaging_products as $packaging ) : ?>

                            <?php
                            if (
                                ! $packaging->is_purchasable() ||
                                ! $packaging->is_in_stock() ||
                                ! $packaging->is_type( 'simple' )
                            ) {
                                continue;
                            }

                            $packaging_price = (float) wc_get_price_to_display(
                                $packaging
                            );
                            ?>

                            <label class="kk-packaging-card">
                                <input
                                    type="radio"
                                    name="kadokita_packaging"
                                    value="<?php echo esc_attr( $packaging->get_id() ); ?>"
                                    data-price="<?php echo esc_attr( $packaging_price ); ?>"
                                    required
                                >

                                <div class="kk-packaging-image">
                                    <?php
                                    echo wp_kses_post(
                                        $packaging->get_image(
                                            'woocommerce_thumbnail'
                                        )
                                    );
                                    ?>
                                </div>

                                <div>
                                    <h3>
                                        <?php echo esc_html( $packaging->get_name() ); ?>
                                    </h3>

                                    <p class="kk-product-price">
                                        <?php echo wp_kses_post( wc_price( $packaging_price ) ); ?>
                                    </p>
                                </div>
                            </label>

                        <?php endforeach; ?>

                    </div>

                <?php endif; ?>
            </section>

             </div>

            <section class="kk-custom-summary">
                <h2>Ringkasan Hampers</h2>

                <div class="kk-summary-row">
                    <span>Jumlah produk dipilih</span>
                    <strong id="kk-item-count">0 produk</strong>
                </div>

                <div class="kk-summary-row">
                    <span>Total isi hampers</span>
                    <strong id="kk-products-total">Rp0</strong>
                </div>

                <div class="kk-summary-row">
                    <span>Harga kemasan</span>
                    <strong id="kk-packaging-total">Rp0</strong>
                </div>

                <div class="kk-summary-total">
                    <span>Estimasi total</span>
                    <strong id="kk-grand-total">Rp0</strong>
                </div>

                <button type="submit" class="kk-custom-submit">
                    Buat Hampers &amp; Lanjut ke Keranjang
                </button>

               
<div id="kk-budget-modal" class="kk-budget-modal" aria-hidden="true">
    <div class="kk-budget-modal-content" role="dialog" aria-modal="true" aria-labelledby="kk-modal-title">
        <div class="kk-modal-icon">!</div>

        <h2 id="kk-modal-title">Budget Hampers Terlampaui</h2>

        <p class="kk-modal-description">
            Total hampers kamu melebihi budget yang dipilih.
        </p>

        <div class="kk-modal-details">
            <div>
                <span>Budget kamu</span>
                <strong id="kk-modal-budget">Rp0</strong>
            </div>
            <div>
                <span>Total hampers</span>
                <strong id="kk-modal-total">Rp0</strong>
            </div>
            <div class="kk-modal-difference">
                <span>Kelebihan budget</span>
                <strong id="kk-modal-difference">Rp0</strong>
            </div>
        </div>

        <p class="kk-modal-question">
            Apakah kamu tetap ingin melanjutkan?
        </p>

        <div class="kk-modal-actions">
            <button type="button" id="kk-modal-cancel">
                Kurangi Produk
            </button>
            <button type="button" id="kk-modal-continue">
                Tetap Lanjut
            </button>
        </div>
    </div>
</div>

            </section>
            </div>

        </form>

    </div>
</main>

<style>
.kk-custom-page {
    background: #FFF8F0;
    color: #3E3033;
    padding-bottom: 60px;
}

.kk-custom-hero {
    background: #F5DDE5;
    text-align: center;
    padding: 55px 20px;
}

.kk-custom-eyebrow {
    color: #8F3D56;
    letter-spacing: 2px;
    font-size: 12px;
    font-weight: 600;
}

.kk-custom-hero h1 {
    color: #3E3033;
    font-size: clamp(28px, 4vw, 40px);
    margin: 10px 0;
}

.kk-custom-hero > p:last-child {
    max-width: 620px;
    margin: 0 auto;
    line-height: 1.8;
}

.kk-custom-container {
    max-width: 1200px;
    width: 92%;
    margin: 40px auto;
}

.kk-custom-section {
    margin-bottom: 45px;
}

.kk-custom-heading {
    display: flex;
    align-items: center;
    gap: 15px;
    margin-bottom: 28px;
}

.kk-custom-heading > span {
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    width: 44px;
    height: 44px;
    border-radius: 50%;
    background: #8F3D56;
    color: #fff;
    font-weight: 600;
}

.kk-custom-heading h2 {
    margin: 0 0 5px;
    font-size: 24px;
    color: #3E3033;
}

.kk-custom-heading p {
    margin: 0;
    color: #6C5A5E;
    font-size: 14px;
}

.kk-product-category {
    margin-bottom: 32px;
}

.kk-product-category > h3 {
    font-size: 19px;
    margin-bottom: 16px;
    color: #8F3D56;
}

.kk-product-grid {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 18px;
}

.kk-product-card {
    display: flex;
    flex-direction: column;
    position: relative;
    background: #fff;
    border: 1px solid #EADDE0;
    border-radius: 12px;
    padding: 12px;
    cursor: pointer;
    transition: border-color .2s, box-shadow .2s;
}

.kk-product-card:has(input:checked),
.kk-packaging-card:has(input:checked) {
    border: 2px solid #8F3D56;
    box-shadow: 0 3px 12px #8F3D5620;
}

.kk-product-checkbox {
    position: absolute;
    top: 12px;
    right: 12px;
    z-index: 1;
    width: 19px;
    height: 19px;
    accent-color: #8F3D56;
}

.kk-product-image img,
.kk-packaging-image img {
    display: block;
    width: 100%;
    height: 180px;
    object-fit: contain;
    border-radius: 8px;
}

.kk-product-info h4 {
    font-size: 15px;
    line-height: 1.5;
    margin: 12px 0 8px;
    color: #3E3033;
}

.kk-product-price {
    color: #8F3D56;
    font-weight: 600;
}

.kk-product-info p {
    color: #6C5A5E;
    font-size: 12px;
    line-height: 1.6;
}

.kk-select-indicator {
    display: block;
    text-align: center;
    border: 1px solid #DFA6B8;
    color: #8F3D56;
    border-radius: 6px;
    padding: 8px;
    margin-top: auto;
    font-size: 13px;
}

.kk-product-card:has(input:checked) .kk-select-indicator {
    background: #8F3D56;
    color: #fff;
}

.kk-packaging-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 18px;
}

.kk-packaging-card {
    display: flex;
    align-items: center;
    gap: 18px;
    position: relative;
    padding: 18px;
    background: #fff;
    border: 1px solid #EADDE0;
    border-radius: 12px;
    cursor: pointer;
}

.kk-packaging-card input {
    accent-color: #8F3D56;
    width: 19px;
    height: 19px;
    flex-shrink: 0;
}

.kk-packaging-image {
    width: 110px;
    flex-shrink: 0;
}

.kk-packaging-image img {
    height: 100px;
}

.kk-packaging-card h3 {
    font-size: 16px;
    margin: 0 0 10px;
}

.kk-packaging-card p {
    margin: 0;
}

.kk-empty-category {
    padding: 15px;
    border-radius: 8px;
    background: #fff;
    color: #6C5A5E;
}

.kk-custom-summary {
    max-width: 600px;
    margin-left: auto;
    padding: 25px;
    background: #fff;
    border: 1px solid #EADDE0;
    border-radius: 14px;
}

.kk-custom-summary h2 {
    margin: 0 0 20px;
    font-size: 23px;
}

.kk-summary-row,
.kk-summary-total {
    display: flex;
    justify-content: space-between;
    gap: 15px;
    padding: 12px 0;
    font-size: 14px;
}

.kk-summary-total {
    margin-top: 8px;
    padding-top: 18px;
    border-top: 1px solid #EADDE0;
    font-size: 17px;
    font-weight: 600;
}

.kk-summary-total strong {
    color: #8F3D56;
}

.kk-custom-submit {
    display: block;
    width: 100%;
    border: none;
    border-radius: 8px;
    padding: 15px;
    margin-top: 20px;
    background: #8F3D56;
    color: #fff;
    font-size: 15px;
    font-weight: 600;
    cursor: pointer;
}

.kk-custom-submit:hover {
    background: #713047;
}

.kk-summary-note {
    color: #6C5A5E;
    font-size: 12px;
    line-height: 1.7;
}

.kk-custom-notice {
    padding: 15px;
    margin-bottom: 25px;
    border: 1px solid #DFA6B8;
    border-radius: 8px;
    background: #fff;
    color: #8F3D56;
}

@media (max-width: 850px) {
    .kk-product-grid {
        grid-template-columns: repeat(3, minmax(0, 1fr));
    }
}

@media (max-width: 600px) {
    .kk-custom-hero {
        padding: 40px 18px;
    }

    .kk-custom-container {
        width: 92%;
        margin-top: 28px;
    }

    .kk-product-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 12px;
    }

    .kk-product-image img {
        height: 135px;
    }

    .kk-packaging-grid {
        grid-template-columns: 1fr;
    }

    .kk-packaging-card {
        gap: 12px;
        padding: 12px;
    }

    .kk-packaging-image {
        width: 85px;
    }

    .kk-custom-heading h2 {
        font-size: 20px;
    }

    .kk-custom-summary {
        padding: 18px;
    }
}
</style>


<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('kk-custom-form');

    if (!form) {
        return;
    }

    const productCheckboxes = form.querySelectorAll('.kk-product-checkbox');
    const packagingRadios = form.querySelectorAll(
        'input[name="kadokita_packaging"]'
    );

    const itemCount = document.getElementById('kk-item-count');
    const productsTotal = document.getElementById('kk-products-total');
    const packagingTotal = document.getElementById('kk-packaging-total');
    const grandTotal = document.getElementById('kk-grand-total');
    const budgetSelect = document.getElementById('kk-budget');
    const budgetStatus = document.getElementById('kk-budget-status');

    const currency = new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        maximumFractionDigits: 0
    });

    
function updateSummary() {
    let count = 0;
    let itemsPrice = 0;
    let packagingPrice = 0;

    productCheckboxes.forEach(function (checkbox) {
        const card = checkbox.closest('.kk-product-card');

        if (!card || !checkbox.checked) {
            return;
        }

        const quantityInput = card.querySelector('.kk-product-quantity');
        let quantity = quantityInput
            ? parseInt(quantityInput.value, 10) || 1
            : 1;

        quantity = Math.max(1, Math.min(99, quantity));

        count += quantity;
        itemsPrice += Number(checkbox.dataset.price || 0) * quantity;
    });

    packagingRadios.forEach(function (radio) {
        if (radio.checked) {
            packagingPrice = Number(radio.dataset.price || 0);
        }
    });

    const total = itemsPrice + packagingPrice;
    const budget = Number(budgetSelect.value || 0);

    itemCount.textContent = count + ' produk';
    productsTotal.textContent = currency.format(itemsPrice);
    packagingTotal.textContent = currency.format(packagingPrice);
    grandTotal.textContent = currency.format(total);

    
     
    if (!budget) {
        budgetStatus.textContent =
            'Budget opsional. Kamu bebas menentukan budget atau langsung membuat hampers.';
    } else if (total > budget) {
        budgetStatus.textContent =
            'Budget terlampaui sebesar ' +
            currency.format(total - budget) + '.';
    } else {
        budgetStatus.textContent =
            'Sisa budget: ' + currency.format(budget - total) + '.';
    }

}


    // Fungsi tombol tambah, kurang, dan input jumlah
    productCheckboxes.forEach(function (checkbox) {
        const card = checkbox.closest('.kk-product-card');

        if (!card) return;

        const quantityInput = card.querySelector('.kk-product-quantity');
        const minusButton = card.querySelector('.kk-quantity-minus');
        const plusButton = card.querySelector('.kk-quantity-plus');
        const indicator = card.querySelector('.kk-select-indicator');

        if (!quantityInput || !minusButton || !plusButton) return;

        function syncQuantity() {
            let quantity = parseInt(quantityInput.value, 10) || 1;
            quantity = Math.max(1, Math.min(99, quantity));

            quantityInput.value = quantity;
            quantityInput.disabled = !checkbox.checked;
            minusButton.disabled = !checkbox.checked || quantity <= 1;
            plusButton.disabled = !checkbox.checked || quantity >= 99;

            if (indicator) {
                indicator.textContent = checkbox.checked ? 'Dipilih' : 'Pilih';
            }
        }

        checkbox.addEventListener('change', function () {
            syncQuantity();
            updateSummary();
        });

        minusButton.addEventListener('click', function (event) {
            event.preventDefault();
            event.stopPropagation();

            if (!checkbox.checked) return;

            quantityInput.value =
                Math.max(1, (parseInt(quantityInput.value, 10) || 1) - 1);

            syncQuantity();
            updateSummary();
        });

        plusButton.addEventListener('click', function (event) {
            event.preventDefault();
            event.stopPropagation();

            if (!checkbox.checked) return;

            quantityInput.value =
                Math.min(99, (parseInt(quantityInput.value, 10) || 1) + 1);

            syncQuantity();
            updateSummary();
        });

        quantityInput.addEventListener('click', function (event) {
            event.stopPropagation();
        });

        quantityInput.addEventListener('change', function () {
            syncQuantity();
            updateSummary();
        });

        syncQuantity();
    });

    packagingRadios.forEach(function (radio) {
        radio.addEventListener('change', updateSummary);
    });

    
form.addEventListener('submit', function (event) {
    const selectedProducts = form.querySelectorAll(
        '.kk-product-checkbox:checked'
    );

    const selectedPackaging = form.querySelector(
        'input[name="kadokita_packaging"]:checked'
    );

    // Pastikan minimal satu produk dipilih
    if (selectedProducts.length === 0) {
        event.preventDefault();
        alert('Pilih minimal satu produk untuk hampers kamu.');
        return;
    }

    // Pastikan kemasan dipilih
    if (!selectedPackaging) {
        event.preventDefault();
        alert('Pilih Hamper Box atau Paper Bag terlebih dahulu.');
        return;
    }

    // Pastikan budget dipilih
    const budget = Number(budgetSelect.value || 0);


    // Hitung total semua produk sesuai jumlahnya
    let productsPrice = 0;

    selectedProducts.forEach(function (checkbox) {
        const card = checkbox.closest('.kk-product-card');
        const quantityInput = card
            ? card.querySelector('.kk-product-quantity')
            : null;

        const quantity = quantityInput
            ? Math.max(
                1,
                Math.min(99, parseInt(quantityInput.value, 10) || 1)
            )
            : 1;

        productsPrice +=
            Number(checkbox.dataset.price || 0) * quantity;
    });

    // Tambahkan harga kemasan
    const packagingPrice = Number(
        selectedPackaging.dataset.price || 0
    );

    const total = productsPrice + packagingPrice;

    
    // Tampilkan popup website jika budget terlampaui
    if (budget > 0 && total > budget) {
        event.preventDefault();

        const modal = document.getElementById('kk-budget-modal');

        document.getElementById('kk-modal-budget').textContent =
            currency.format(budget);

        document.getElementById('kk-modal-total').textContent =
            currency.format(total);

        document.getElementById('kk-modal-difference').textContent =
            currency.format(total - budget);

        modal.classList.add('is-visible');
        modal.setAttribute('aria-hidden', 'false');
    }


    // Jika budget tidak terlampaui atau pembeli memilih OK,
    // form akan dilanjutkan seperti biasa.
});


    
    // Hubungkan tombol popup konfirmasi budget
    const budgetModal = document.getElementById('kk-budget-modal');
    const cancelModal = document.getElementById('kk-modal-cancel');
    const continueModal = document.getElementById('kk-modal-continue');

    function closeBudgetModal() {
        budgetModal.classList.remove('is-visible');
        budgetModal.setAttribute('aria-hidden', 'true');
    }

    // Tombol Kurangi Produk
    if (cancelModal) {
        cancelModal.addEventListener('click', function () {
            closeBudgetModal();
        });
    }

    // Tombol Tetap Lanjut
    if (continueModal) {
        continueModal.addEventListener('click', function () {
            const formElement = document.getElementById('kk-custom-form');

            closeBudgetModal();

            if (formElement) {
                HTMLFormElement.prototype.submit.call(formElement);
            }
        });
    }

    budgetSelect.addEventListener('change', updateSummary);
    updateSummary();
});
</script>



<?php get_footer(); ?>
