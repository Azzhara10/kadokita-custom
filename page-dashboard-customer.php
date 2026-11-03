
<?php
/*
Template Name: Dashboard Customer KadoKita
*/

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! is_user_logged_in() ) {
    wp_safe_redirect( wc_get_page_permalink( 'myaccount' ) );
    exit;
}

$current_user = wp_get_current_user();

$bagian = isset( $_GET['bagian'] )
    ? sanitize_key( wp_unslash( $_GET['bagian'] ) )
    : 'dashboard';

$menu_customer = array(
    'dashboard',
    'pesanan',
    'keranjang',
    'profil',
    'ubah-kata-sandi'
);

if ( ! in_array( $bagian, $menu_customer, true ) ) {
    $bagian = 'dashboard';
}

$dashboard_url = get_permalink();

/* PROSES SIMPAN PROFIL CUSTOMER */
$pesan_profil = '';
$error_profil = '';

if (
    isset( $_POST['kk_simpan_profil'] ) &&
    is_user_logged_in()
) {
    $nonce = isset( $_POST['kk_customer_profile_nonce'] )
        ? sanitize_text_field( wp_unslash( $_POST['kk_customer_profile_nonce'] ) )
        : '';

    if ( ! wp_verify_nonce( $nonce, 'kk_simpan_profil_customer' ) ) {
        $error_profil = 'Sesi tidak valid. Silakan muat ulang halaman.';
    } else {
        $nama_depan = isset( $_POST['nama_depan'] )
            ? sanitize_text_field( wp_unslash( $_POST['nama_depan'] ) )
            : '';

        $nama_belakang = isset( $_POST['nama_belakang'] )
            ? sanitize_text_field( wp_unslash( $_POST['nama_belakang'] ) )
            : '';

        $email = isset( $_POST['email_customer'] )
            ? sanitize_email( wp_unslash( $_POST['email_customer'] ) )
            : '';

        $telepon = isset( $_POST['telepon_customer'] )
            ? sanitize_text_field( wp_unslash( $_POST['telepon_customer'] ) )
            : '';

        $jenis_kelamin = isset( $_POST['jenis_kelamin'] )
            ? sanitize_text_field( wp_unslash( $_POST['jenis_kelamin'] ) )
            : '';

        $alamat = isset( $_POST['alamat_customer'] )
            ? sanitize_text_field( wp_unslash( $_POST['alamat_customer'] ) )
            : '';

        $kota = isset( $_POST['kota_customer'] )
            ? sanitize_text_field( wp_unslash( $_POST['kota_customer'] ) )
            : '';

        if ( ! is_email( $email ) ) {
            $error_profil = 'Format email tidak valid.';
        } elseif (
            email_exists( $email ) &&
            strtolower( $email ) !== strtolower( $current_user->user_email )
        ) {
            $error_profil = 'Email tersebut sudah digunakan akun lain.';
        } elseif (
            ! in_array( $jenis_kelamin, array( '', 'Laki-laki', 'Perempuan' ), true )
        ) {
            $error_profil = 'Pilihan jenis kelamin tidak valid.';
        } else {
            $hasil_update = wp_update_user( array(
                'ID'           => $current_user->ID,
                'first_name'   => $nama_depan,
                'last_name'    => $nama_belakang,
                'display_name' => trim( $nama_depan . ' ' . $nama_belakang )
                    ?: $current_user->user_login,
                'user_email'   => $email,
            ) );

            if ( is_wp_error( $hasil_update ) ) {
                $error_profil = 'Profil gagal disimpan. Silakan coba lagi.';
            } else {
                update_user_meta( $current_user->ID, 'billing_phone', $telepon );
                update_user_meta( $current_user->ID, 'kk_nomor_wa', $telepon );
                update_user_meta( $current_user->ID, 'kk_jenis_kelamin', $jenis_kelamin );
                update_user_meta( $current_user->ID, 'billing_address_1', $alamat );
                update_user_meta( $current_user->ID, 'billing_city', $kota );

                
/* PROSES UPLOAD FOTO PROFIL */
if (
    isset( $_FILES['kk_foto_profil'] ) &&
    ! empty( $_FILES['kk_foto_profil']['name'] )
) {
    require_once ABSPATH . 'wp-admin/includes/file.php';
    require_once ABSPATH . 'wp-admin/includes/media.php';
    require_once ABSPATH . 'wp-admin/includes/image.php';

    $foto_upload = media_handle_upload(
        'kk_foto_profil',
        0
    );

    if ( ! is_wp_error( $foto_upload ) ) {
        $url_foto = wp_get_attachment_url( $foto_upload );

        if ( $url_foto ) {
            update_user_meta(
                $current_user->ID,
                'kk_foto_profil',
                esc_url_raw( $url_foto )
            );
        }
    }
}


                $pesan_profil = 'Profil berhasil disimpan.';

                $current_user = wp_get_current_user();
            }
        }
    }
}


/* PROSES UBAH KATA SANDI CUSTOMER */
$pesan_password = '';
$error_password = '';

if (
    isset( $_POST['kk_ubah_password'] ) &&
    is_user_logged_in()
) {
    $nonce_password = isset( $_POST['kk_password_nonce'] )
        ? sanitize_text_field( wp_unslash( $_POST['kk_password_nonce'] ) )
        : '';

    $password_lama = isset( $_POST['password_lama'] )
        ? (string) wp_unslash( $_POST['password_lama'] )
        : '';

    $password_baru = isset( $_POST['password_baru'] )
        ? (string) wp_unslash( $_POST['password_baru'] )
        : '';

    $konfirmasi_password = isset( $_POST['konfirmasi_password'] )
        ? (string) wp_unslash( $_POST['konfirmasi_password'] )
        : '';

    if ( ! wp_verify_nonce( $nonce_password, 'kk_ubah_password_customer' ) ) {
        $error_password = 'Sesi tidak valid. Silakan muat ulang halaman.';
    } elseif (
        ! wp_check_password(
            $password_lama,
            $current_user->user_pass,
            $current_user->ID
        )
    ) {
        $error_password = 'Kata sandi saat ini tidak sesuai.';
    } elseif ( strlen( $password_baru ) < 8 ) {
        $error_password = 'Kata sandi baru minimal 8 karakter.';
    } elseif ( $password_baru !== $konfirmasi_password ) {
        $error_password = 'Konfirmasi kata sandi baru tidak cocok.';
    } elseif ( $password_lama === $password_baru ) {
        $error_password = 'Kata sandi baru harus berbeda dari kata sandi lama.';
    } else {
        $hasil_password = wp_update_user( array(
            'ID'        => $current_user->ID,
            'user_pass' => $password_baru,
        ) );

        if ( is_wp_error( $hasil_password ) ) {
            $error_password = 'Kata sandi gagal diperbarui. Silakan coba lagi.';
        } else {
            wp_set_auth_cookie(
                $current_user->ID,
                is_user_logged_in()
            );

            $pesan_password = 'Kata sandi berhasil diperbarui.';
            $current_user = wp_get_current_user();
        }
    }
}


get_header();
?>

<div class="kk-customer-dashboard">

    <aside class="kk-customer-sidebar">

        <div class="kk-customer-brand">
            <h2>KadoKita</h2>
            <p>Customer Dashboard</p>
        </div>

        <nav class="kk-customer-menu">

            <a
                class="kk-customer-menu-link <?php echo $bagian === 'dashboard' ? 'active' : ''; ?>"
                href="<?php echo esc_url( add_query_arg( 'bagian', 'dashboard', $dashboard_url ) ); ?>"
            >
                <i class="fa-solid fa-house"></i>
                <span>Dashboard</span>
            </a>

            <a
                class="kk-customer-menu-link <?php echo $bagian === 'pesanan' ? 'active' : ''; ?>"
                href="<?php echo esc_url( add_query_arg( 'bagian', 'pesanan', $dashboard_url ) ); ?>"
            >
                <i class="fa-solid fa-box"></i>
                <span>Pesanan</span>
            </a>

            <a
                class="kk-customer-menu-link <?php echo $bagian === 'keranjang' ? 'active' : ''; ?>"
                href="<?php echo esc_url( add_query_arg( 'bagian', 'keranjang', $dashboard_url ) ); ?>"
            >
                <i class="fa-solid fa-cart-shopping"></i>
                <span>Keranjang</span>
            </a>

            <a
                class="kk-customer-menu-link <?php echo $bagian === 'profil' ? 'active' : ''; ?>"
                href="<?php echo esc_url( add_query_arg( 'bagian', 'profil', $dashboard_url ) ); ?>"
            >
                <i class="fa-solid fa-user"></i>
                <span>Profil Pengguna</span>
            </a>

            <a
                class="kk-customer-menu-link"
                href="<?php echo esc_url( wc_logout_url( $dashboard_url ) ); ?>"
            >
                <i class="fa-solid fa-right-from-bracket"></i>
                <span>Keluar</span>
            </a>

        </nav>
    </aside>

    <main class="kk-customer-main">

        <header class="kk-customer-header">
            <div>
                <h1>
                    <?php
                    $judul = array(
                        'dashboard' => 'Dashboard Customer',
                        'pesanan'   => 'Pesanan Saya',
                        'keranjang' => 'Keranjang Saya',
                        'profil'    => 'Profil Pengguna',
                        'ubah-kata-sandi'  => 'Ubah Kata Sandi',
                    );

                    echo esc_html( $judul[ $bagian ] );
                    ?>
                </h1>

                <p>Selamat datang kembali di KadoKita.</p>
            </div>

            <div class="kk-customer-user">
                <i class="fa-solid fa-circle-user"></i>
                <span><?php echo esc_html( $current_user->display_name ); ?></span>
            </div>
        </header>

        
<?php if ( 'dashboard' === $bagian ) : ?>
    <?php
    $customer_id = $current_user->ID;
    $foto_profil_dashboard = get_user_meta(
    $customer_id,
    'kk_foto_profil',
    true
);
    

    $nama_lengkap = trim(
        get_user_meta( $customer_id, 'first_name', true ) . ' ' .
        get_user_meta( $customer_id, 'last_name', true )
    );

    if ( empty( $nama_lengkap ) ) {
        $nama_lengkap = $current_user->display_name;
    }

    $nomor_telepon = get_user_meta(
        $customer_id,
        'billing_phone',
        true
    );

    $alamat = array_filter( array(
        get_user_meta( $customer_id, 'billing_address_1', true ),
        get_user_meta( $customer_id, 'billing_city', true ),
        get_user_meta( $customer_id, 'billing_state', true ),
        get_user_meta( $customer_id, 'billing_postcode', true ),
    ) );

    $alamat_lengkap = implode( ', ', $alamat );

    $tanggal_bergabung = date_i18n(
        'd F Y',
        strtotime( $current_user->user_registered )
    );
    ?>

    <section class="kk-customer-card kk-customer-identity">

    <div class="kk-customer-profile-photo">
    <?php if ( ! empty( $foto_profil_dashboard ) ) : ?>
        <img
            src="<?php echo esc_url( $foto_profil_dashboard ); ?>"
            alt="Foto Profil <?php echo esc_attr( $current_user->display_name ); ?>"
            class="kk-customer-avatar"
        >
    <?php else : ?>
        <?php
        echo get_avatar(
            $current_user->ID,
            120,
            '',
            'Foto Profil ' . $current_user->display_name,
            array(
                'class' => 'kk-customer-avatar',
            )
        );
        ?>
    <?php endif; ?>

    <div class="kk-customer-profile-name">
        <h2><?php echo esc_html( $nama_lengkap ); ?></h2>
        <p>Customer KadoKita</p>
    </div>
</div>

    <h2>Identitas Pengguna</h2>

        <p class="kk-customer-identity-description">
            Informasi akun kamu yang terdaftar di KadoKita.
        </p>

        <div class="kk-customer-identity-list">
            <div class="kk-customer-identity-item">
                <span>Nama Lengkap</span>
                <strong><?php echo esc_html( $nama_lengkap ); ?></strong>
            </div>

            <div class="kk-customer-identity-item">
                <span>Username</span>
                <strong><?php echo esc_html( $current_user->user_login ); ?></strong>
            </div>

            <div class="kk-customer-identity-item">
                <span>Email</span>
                <strong><?php echo esc_html( $current_user->user_email ); ?></strong>
            </div>

            
<div class="kk-customer-identity-item">
    <span>Jenis Kelamin</span>
    <strong>
        <?php
        $jenis_kelamin_dashboard = get_user_meta(
            $current_user->ID,
            'kk_jenis_kelamin',
            true
        );

        echo esc_html( $jenis_kelamin_dashboard ?: 'Belum diisi' );
        ?>
    </strong>
</div>


            <div class="kk-customer-identity-item">
                <span>Nomor Telepon</span>
                <strong>
                    <?php echo esc_html( $nomor_telepon ?: 'Belum diisi' ); ?>
                </strong>
            </div>

            <div class="kk-customer-identity-item">
                <span>Alamat</span>
                <strong>
                    <?php echo esc_html( $alamat_lengkap ?: 'Belum diisi' ); ?>
                </strong>
            </div>

            <div class="kk-customer-identity-item">
                <span>Tanggal Bergabung</span>
                <strong><?php echo esc_html( $tanggal_bergabung ); ?></strong>
            </div>

        </div>

        <a
            class="kk-customer-edit-button"
            href="<?php echo esc_url( add_query_arg( 'bagian', 'profil', $dashboard_url ) ); ?>"
        >
            <i class="fa-solid fa-pen-to-square"></i>
            Edit Profil
        </a>

    </section>

        <?php elseif ( 'pesanan' === $bagian ) : ?>

    <section class="kk-customer-card kk-customer-orders">
        <h2>Riwayat Pesanan</h2>
        <p class="kk-orders-description">
            Berikut daftar pesanan yang pernah kamu lakukan di KadoKita.
        </p>

        <?php
        if ( function_exists( 'wc_get_orders' ) ) {
            $pesanan_customer = wc_get_orders( array(
                'customer_id' => $current_user->ID,
                'limit'       => -1,
                'orderby'     => 'date',
                'order'       => 'DESC',
            ) );

            if ( ! empty( $pesanan_customer ) ) :
        ?>
                <div class="kk-orders-table-wrapper">
                    <table class="kk-orders-table">
                        <thead>
                            <tr>
                                <th>No. Pesanan</th>
                                <th>Tanggal</th>
                                <th>Status</th>
                                <th>Total</th>
                                <th>Detail</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ( $pesanan_customer as $pesanan ) : ?>
                                <tr>
                                    <td>
                                        #<?php echo esc_html( $pesanan->get_order_number() ); ?>
                                    </td>
                                    <td>
                                        <?php
                                        $tanggal_pesanan = $pesanan->get_date_created();
                                        echo $tanggal_pesanan
                                            ? esc_html( wc_format_datetime( $tanggal_pesanan, 'd M Y' ) )
                                            : '-';
                                        ?>
                                    </td>
                                    <td>
                                        <span class="kk-order-status">
                                            <?php echo esc_html( wc_get_order_status_name( $pesanan->get_status() ) ); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php echo wp_kses_post( $pesanan->get_formatted_order_total() ); ?>
                                    </td>
                                    <td>
                                        <a
                                            class="kk-order-detail-button"
                                            href="<?php echo esc_url( $pesanan->get_view_order_url() ); ?>"
                                        >
                                            Lihat
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
        <?php
            else :
        ?>
                <div class="kk-orders-empty">
                    <i class="fa-solid fa-box-open"></i>
                    <p>Kamu belum memiliki pesanan.</p>
                    <a href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>">
                        Mulai Belanja
                    </a>
                </div>
        <?php
            endif;
        }
        ?>
    </section>

        <?php elseif ( 'keranjang' === $bagian ) : ?>

            <section class="kk-customer-card">
                <h2>Keranjang Belanja</h2>
                <?php
                echo do_shortcode( '[woocommerce_cart]' );
                ?>
            </section>

        

<?php elseif ( 'profil' === $bagian ) : ?>

    <?php
    $nama_depan = get_user_meta( $current_user->ID, 'first_name', true );
    $nama_belakang = get_user_meta( $current_user->ID, 'last_name', true );

    $telepon = get_user_meta( $current_user->ID, 'billing_phone', true );
    if ( ! $telepon ) {
        $telepon = get_user_meta( $current_user->ID, 'kk_nomor_wa', true );
    }

    $jenis_kelamin = get_user_meta( $current_user->ID, 'kk_jenis_kelamin', true );
    $alamat = get_user_meta( $current_user->ID, 'billing_address_1', true );
    $kota = get_user_meta( $current_user->ID, 'billing_city', true );

    $foto_profil = get_user_meta( $current_user->ID, 'kk_foto_profil', true );
    ?>

    <section class="kk-customer-card kk-customer-profile">

        <h2>Identitas Pengguna</h2>
        <p>Perbarui informasi akun kamu melalui formulir berikut.</p>

        <?php if ( $pesan_profil ) : ?>
            <p class="kk-customer-profile-success">
                <?php echo esc_html( $pesan_profil ); ?>
            </p>
        <?php endif; ?>

        <?php if ( $error_profil ) : ?>
            <p class="kk-customer-profile-error">
                <?php echo esc_html( $error_profil ); ?>
            </p>
        <?php endif; ?>

        <form method="post" enctype="multipart/form-data" class="kk-customer-profile-form">

            <?php wp_nonce_field( 'kk_simpan_profil_customer', 'kk_customer_profile_nonce' ); ?>

                
<div class="kk-customer-photo-upload">

    <label for="kk-foto-profil">
        Foto Profil
    </label>

    <?php if ( ! empty( $foto_profil ) ) : ?>
        <img
            src="<?php echo esc_url( $foto_profil ); ?>"
            alt="Foto profil saat ini"
            class="kk-customer-avatar-preview"
        >
    <?php else : ?>
        <?php
        echo get_avatar(
            $current_user->ID,
            120,
            '',
            'Foto profil saat ini',
            array( 'class' => 'kk-customer-avatar-preview' )
        );
        ?>
    <?php endif; ?>

    <input
        type="file"
        id="kk-foto-profil"
        name="kk_foto_profil"
        accept="image/jpeg,image/png,image/webp"
    >

    <small>Format JPG, PNG, atau WebP.</small>

</div>


            <div class="kk-customer-profile-fields">

                <label>
                    Nama Depan
                    <input
                        type="text"
                        name="nama_depan"
                        value="<?php echo esc_attr( $nama_depan ); ?>"
                        required
                    >
                </label>

                <label>
                    Nama Belakang
                    <input
                        type="text"
                        name="nama_belakang"
                        value="<?php echo esc_attr( $nama_belakang ); ?>"
                    >
                </label>

                <label>
                    Username
                    <input
                        type="text"
                        value="<?php echo esc_attr( $current_user->user_login ); ?>"
                        readonly
                    >
                </label>

                <label>
                    Email
                    <input
                        type="email"
                        name="email_customer"
                        value="<?php echo esc_attr( $current_user->user_email ); ?>"
                        required
                    >
                </label>

                <label>
                    Nomor WhatsApp
                    <input
                        type="tel"
                        name="telepon_customer"
                        value="<?php echo esc_attr( $telepon ); ?>"
                        placeholder="Contoh: 081234567890"
                    >
                </label>

                <label>
                    Jenis Kelamin
                    <select name="jenis_kelamin">
                        <option value="">Pilih jenis kelamin</option>
                        <option value="Laki-laki" <?php selected( $jenis_kelamin, 'Laki-laki' ); ?>>
                            Laki-laki
                        </option>
                        <option value="Perempuan" <?php selected( $jenis_kelamin, 'Perempuan' ); ?>>
                            Perempuan
                        </option>
                    </select>
                </label>

                <label>
                    Alamat
                    <input
                        type="text"
                        name="alamat_customer"
                        value="<?php echo esc_attr( $alamat ); ?>"
                        placeholder="Nama jalan dan nomor rumah"
                    >
                </label>

                <label>
                    Kota
                    <input
                        type="text"
                        name="kota_customer"
                        value="<?php echo esc_attr( $kota ); ?>"
                        placeholder="Contoh: Makassar"
                    >
                </label>

            </div>

            <button type="submit" name="kk_simpan_profil" value="1">
                <i class="fa-solid fa-floppy-disk"></i>
                Simpan Perubahan
            </button>

        </form>

        <p>
            <a href="<?php echo esc_url( add_query_arg( 'bagian', 'ubah-kata-sandi',    $dashboard_url ) ); ?>">
                Ubah kata sandi
            </a>
        </p>

    </section>


<?php elseif ( 'ubah-kata-sandi' === $bagian ) : ?>

    <section class="kk-customer-card kk-password-card">

        <h2>Ubah Kata Sandi</h2>

        <p class="kk-password-description">
            Perbarui kata sandi untuk menjaga keamanan akun KadoKita kamu.
        </p>

        <?php if ( $pesan_password ) : ?>
            <p class="kk-password-success">
                <?php echo esc_html( $pesan_password ); ?>
            </p>
        <?php endif; ?>

        <?php if ( $error_password ) : ?>
            <p class="kk-password-error">
                <?php echo esc_html( $error_password ); ?>
            </p>
        <?php endif; ?>

        <form method="post" class="kk-password-form">

            <?php
            wp_nonce_field(
                'kk_ubah_password_customer',
                'kk_password_nonce'
            );
            ?>

            <label>
                Kata Sandi Saat Ini
                <input
                    type="password"
                    name="password_lama"
                    autocomplete="current-password"
                    required
                >
            </label>

            <label>
                Kata Sandi Baru
                <input
                    type="password"
                    name="password_baru"
                    autocomplete="new-password"
                    minlength="8"
                    required
                >
                <small>Minimal 8 karakter.</small>
            </label>

            <label>
                Konfirmasi Kata Sandi Baru
                <input
                    type="password"
                    name="konfirmasi_password"
                    autocomplete="new-password"
                    minlength="8"
                    required
                >
            </label>

            <button
                type="submit"
                name="kk_ubah_password"
                value="1"
            >
                Simpan Kata Sandi
            </button>

            <a
                class="kk-password-back"
                href="<?php echo esc_url( add_query_arg( 'bagian', 'profil', $dashboard_url ) ); ?>"
            >
                Kembali ke Profil
            </a>

        </form>

    </section>


<?php endif; ?>


    </main>

</div>

<?php get_footer(); ?>
