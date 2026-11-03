
<?php
/**
 * Template Name: Dashboard Admin KadoKita
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! is_user_logged_in() ) {
    wp_safe_redirect( wp_login_url( get_permalink() ) );
    exit;
}

if ( ! current_user_can( 'manage_options' ) ) {
    wp_die( 'Akses ditolak. Halaman ini khusus Administrator.' );
}


if ( isset( $_POST['kk_simpan_profil'] ) ) {

    if (
        ! isset( $_POST['kk_nonce_profil'] ) ||
        ! wp_verify_nonce(
            sanitize_text_field( wp_unslash( $_POST['kk_nonce_profil'] ) ),
            'kk_simpan_profil'
        )
    ) {
        wp_die( 'Validasi keamanan gagal. Silakan coba lagi.' );
    }

    
$admin_id = get_current_user_id();

    $nama_admin = isset( $_POST['kk_nama_admin'] )
        ? sanitize_text_field( wp_unslash( $_POST['kk_nama_admin'] ) )
        : '';

    $nomor_wa = isset( $_POST['kk_nomor_wa'] )
        ? sanitize_text_field( wp_unslash( $_POST['kk_nomor_wa'] ) )
        : '';

    $jenis_kelamin = isset( $_POST['kk_jenis_kelamin'] )
        ? sanitize_text_field( wp_unslash( $_POST['kk_jenis_kelamin'] ) )
        : '';

    $email_admin = isset( $_POST['kk_email_admin'] )
        ? sanitize_email( wp_unslash( $_POST['kk_email_admin'] ) )
        : '';

    if ( '' === $nama_admin ) {
        wp_die( 'Nama admin wajib diisi.' );
    }

    if ( ! in_array( $jenis_kelamin, array( '', 'Laki-laki', 'Perempuan' ), true ) ) {
        wp_die( 'Pilihan jenis kelamin tidak valid.' );
    }

    if ( ! is_email( $email_admin ) ) {
        wp_die( 'Format email tidak valid.' );
    }

    $email_pemilik = email_exists( $email_admin );

    if ( $email_pemilik && (int) $email_pemilik !== $admin_id ) {
        wp_die( 'Email tersebut sudah digunakan akun lain.' );
    }

    
    // Hapus foto profil jika diminta.
    if (
        isset( $_POST['kk_hapus_foto_profil'] ) &&
        '1' === $_POST['kk_hapus_foto_profil']
    ) {
        delete_user_meta( $admin_id, 'kk_foto_profil' );
    }



    // Simpan foto profil jika admin mengunggah foto baru.
    
    if (
        ! isset( $_POST['kk_hapus_foto_profil'] ) &&
        isset( $_FILES['kk_foto_profil'] ) &&
        isset( $_FILES['kk_foto_profil']['error'] ) &&
        UPLOAD_ERR_OK === $_FILES['kk_foto_profil']['error']
    ) {

        $file = $_FILES['kk_foto_profil'];

        // Batasi ukuran foto maksimal 2 MB.
        if ( $file['size'] > 2 * 1024 * 1024 ) {
            wp_die( 'Ukuran foto maksimal 2 MB.' );
        }

        require_once ABSPATH . 'wp-admin/includes/file.php';

        $tipe_file = wp_check_filetype_and_ext(
            $file['tmp_name'],
            $file['name']
        );

        $tipe_diizinkan = array( 'image/jpeg', 'image/png', 'image/webp' );

        if (
            empty( $tipe_file['type'] ) ||
            ! in_array( $tipe_file['type'], $tipe_diizinkan, true )
        ) {
            wp_die( 'Format foto harus JPG, PNG, atau WebP.' );
        }

        $unggahan = wp_handle_upload(
            $file,
            array(
                'test_form' => false,
                'mimes'     => array(
                    'jpg|jpeg|jpe' => 'image/jpeg',
                    'png'          => 'image/png',
                    'webp'         => 'image/webp',
                ),
            )
        );

        if ( isset( $unggahan['error'] ) ) {
            wp_die( 'Foto gagal diunggah: ' . esc_html( $unggahan['error'] ) );
        }

        update_user_meta(
            $admin_id,
            'kk_foto_profil',
            esc_url_raw( $unggahan['url'] )
        );
    } elseif (
        isset( $_FILES['kk_foto_profil']['error'] ) &&
        UPLOAD_ERR_NO_FILE !== $_FILES['kk_foto_profil']['error']
    ) {
        wp_die( 'Foto gagal diunggah. Silakan coba kembali.' );
    }



    $hasil = wp_update_user( array(
    'ID'           => $admin_id,
    'display_name' => $nama_admin,
    'user_email'   => $email_admin,
) );

if ( ! is_wp_error( $hasil ) ) {
    update_user_meta( $admin_id, 'kk_nomor_wa', $nomor_wa );
    update_user_meta( $admin_id, 'kk_jenis_kelamin', $jenis_kelamin );
}

    if ( is_wp_error( $hasil ) ) {
        wp_die( 'Profil gagal disimpan. Silakan periksa kembali.' );
    }

    wp_safe_redirect(
        add_query_arg(
            'status_profil',
            'berhasil',
            remove_query_arg( 'status_profil' )
        )
    );
    exit;
}




/* Simpan pengaturan website KadoKita */
if (
    isset( $_POST['kk_simpan_pengaturan'] ) &&
    isset( $_POST['kk_nonce_pengaturan'] )
) {
    if (
        ! wp_verify_nonce(
            sanitize_text_field( wp_unslash( $_POST['kk_nonce_pengaturan'] ) ),
            'kk_simpan_pengaturan'
        )
    ) {
        wp_die( 'Permintaan tidak valid. Silakan muat ulang halaman.' );
    }

    update_option(
        'kk_nama_toko',
        sanitize_text_field( wp_unslash( $_POST['kk_nama_toko'] ?? 'KadoKita' ) )
    );

    update_option(
        'kk_deskripsi_toko',
        sanitize_textarea_field( wp_unslash( $_POST['kk_deskripsi_toko'] ?? '' ) )
    );

    update_option(
        'kk_email_toko',
        sanitize_email( wp_unslash( $_POST['kk_email_toko'] ?? '' ) )
    );

    update_option(
        'kk_telepon_toko',
        sanitize_text_field( wp_unslash( $_POST['kk_telepon_toko'] ?? '' ) )
    );



    wp_safe_redirect(
        add_query_arg(
            array(
                'bagian' => 'pengaturan',
                'status_pengaturan' => 'berhasil',
            ),
            get_permalink()
        )
    );
    exit;
}




if (
    isset( $_POST['kk_aksi_kategori'] ) &&
    isset( $_POST['kk_nonce_kategori'] ) &&
    wp_verify_nonce(
        sanitize_text_field( wp_unslash( $_POST['kk_nonce_kategori'] ) ),
        'kk_kelola_kategori'
    )
) {
    $aksi = sanitize_key( wp_unslash( $_POST['kk_aksi_kategori'] ) );

    if ( current_user_can( 'manage_options' ) ) {
        $status = 'gagal';

        if ( 'tambah' === $aksi ) {
            $nama = isset( $_POST['nama_kategori'] )
                ? sanitize_text_field( wp_unslash( $_POST['nama_kategori'] ) )
                : '';

            if ( '' !== $nama ) {
                $hasil = wp_insert_term( $nama, 'product_cat' );

                if ( ! is_wp_error( $hasil ) ) {
                    $status = 'berhasil_tambah';
                }
            }
        } elseif ( 'edit' === $aksi ) {
            $id = isset( $_POST['id_kategori'] )
                ? absint( $_POST['id_kategori'] )
                : 0;

            $nama = isset( $_POST['nama_kategori'] )
                ? sanitize_text_field( wp_unslash( $_POST['nama_kategori'] ) )
                : '';

            if ( $id && '' !== $nama ) {
                $hasil = wp_update_term(
                    $id,
                    'product_cat',
                    array( 'name' => $nama )
                );

                if ( ! is_wp_error( $hasil ) ) {
                    $status = 'berhasil_edit';
                }
            }
        } elseif ( 'hapus' === $aksi ) {
            $id = isset( $_POST['id_kategori'] )
                ? absint( $_POST['id_kategori'] )
                : 0;

            if ( $id ) {
                $hasil = wp_delete_term( $id, 'product_cat' );

                if ( ! is_wp_error( $hasil ) && $hasil ) {
                    $status = 'berhasil_hapus';
                }
            }
        }

        wp_safe_redirect(
            add_query_arg(
                array(
                    'bagian' => 'kategori',
                    'status_kategori' => $status,
                ),
                get_permalink()
            )
        );
        exit;
    }
}



wp_enqueue_style(
    'kadokita-admin-dashboard',
    get_stylesheet_directory_uri() . '/dashboard.css',
    array(),
    '1.0.0'
);

get_header();

$bagian = isset( $_GET['bagian'] )
    ? sanitize_key( $_GET['bagian'] )
    : 'dashboard';

$menu_valid = array(
    'dashboard',
    'pengguna',
    'produk',
    'kategori',
    'pesanan',
    'pengaturan',
    'profil'
);

if ( ! in_array( $bagian, $menu_valid, true ) ) {
    $bagian = 'dashboard';
}

$judul_menu = array(
    'dashboard'  => 'Dashboard',
    'pengguna'   => 'Pengguna',
    'produk'     => 'Daftar Produk',
    'kategori'   => 'Kategori Produk',
    'pesanan'    => 'Cek Pesanan',
    'pengaturan' => 'Pengaturan Web',
    'profil'     => 'Profil Admin'
);
?>

<div class="kk-admin-dashboard">

    <aside class="kk-admin-sidebar">

        <div class="kk-admin-brand">
            <h2>KadoKita</h2>
            <p>Admin Dashboard</p>
        </div>

        <nav class="kk-admin-menu">
            <p class="kk-menu-heading">MENU UTAMA</p>

            <a class="kk-menu-link <?php echo $bagian === 'dashboard' ? 'active' : ''; ?>"
               href="<?php echo esc_url( get_permalink() ); ?>">
                <i class="fa-solid fa-house"></i>
                <span>Dashboard</span>
            </a>

            <p class="kk-menu-heading">PENGELOLAAN</p>

            <a class="kk-menu-link <?php echo $bagian === 'pengguna' ? 'active' : ''; ?>"
               href="<?php echo esc_url( add_query_arg( 'bagian', 'pengguna', get_permalink() ) ); ?>">
                <i class="fa-solid fa-users"></i>
                <span>Pengguna</span>
            </a>

            <a class="kk-menu-link <?php echo $bagian === 'produk' ? 'active' : ''; ?>"
               href="<?php echo esc_url( add_query_arg( 'bagian', 'produk', get_permalink() ) ); ?>">
                <i class="fa-solid fa-box"></i>
                <span>Daftar Produk</span>
            </a>

            <a class="kk-menu-link <?php echo $bagian === 'kategori' ? 'active' : ''; ?>"
               href="<?php echo esc_url( add_query_arg( 'bagian', 'kategori', get_permalink() ) ); ?>">
                <i class="fa-solid fa-tags"></i>
                <span>Kategori Produk</span>
            </a>

            <a class="kk-menu-link <?php echo $bagian === 'pesanan' ? 'active' : ''; ?>"
               href="<?php echo esc_url( add_query_arg( 'bagian', 'pesanan', get_permalink() ) ); ?>">
                <i class="fa-solid fa-cart-shopping"></i>
                <span>Cek Pesanan</span>
            </a>

            <p class="kk-menu-heading">PENGATURAN</p>

            <a class="kk-menu-link <?php echo $bagian === 'pengaturan' ? 'active' : ''; ?>"
               href="<?php echo esc_url( add_query_arg( 'bagian', 'pengaturan', get_permalink() ) ); ?>">
                <i class="fa-solid fa-gear"></i>
                <span>Pengaturan Web</span>
            </a>

            <a class="kk-menu-link <?php echo $bagian === 'profil' ? 'active' : ''; ?>"
               href="<?php echo esc_url( add_query_arg( 'bagian', 'profil', get_permalink() ) ); ?>">
                <i class="fa-solid fa-user"></i>
                <span>Profil Admin</span>
            </a>

            <a class="kk-menu-link"
               href="<?php echo esc_url( wp_logout_url( home_url( '/' ) ) ); ?>">
                <i class="fa-solid fa-right-from-bracket"></i>
                <span>Keluar</span>
            </a>
        </nav>

    </aside>

    <main class="kk-admin-main">

        <header class="kk-admin-header">
            <div>
                <h1><?php echo esc_html( $judul_menu[$bagian] ); ?></h1>
                <p>Pengelolaan website KadoKita.</p>
            </div>

            <div class="kk-admin-user">
                <?php
                $current_user = wp_get_current_user();
                echo get_avatar( $current_user->ID, 40 );
                ?>
                <span><?php echo esc_html( $current_user->display_name ); ?></span>
            </div>
        </header>

        
<?php if ( $bagian === 'dashboard' ) : ?>

    <?php
    $admin_dashboard = wp_get_current_user();
    $foto_admin = get_user_meta( $admin_dashboard->ID, 'kk_foto_profil', true );
    $whatsapp_admin = get_user_meta( $admin_dashboard->ID, 'kk_nomor_wa', true );
    $gender_admin = get_user_meta( $admin_dashboard->ID, 'kk_jenis_kelamin', true );
    ?>

    <section class="kk-admin-welcome">
        <h2>Selamat Datang <?php echo esc_html( $admin_dashboard->display_name ); ?></h2>
    </section>

    <section class="kk-admin-identitas">
        <h3>Identitas Admin</h3>

        <div class="kk-admin-identitas-foto">
            <?php if ( ! empty( $foto_admin ) ) : ?>
                <img
                    src="<?php echo esc_url( $foto_admin ); ?>"
                    alt="Foto profil admin"
                    width="100"
                    height="100"
                >
            <?php else : ?>
                <?php echo get_avatar( $admin_dashboard->ID, 100 ); ?>
            <?php endif; ?>
        </div>

        <div class="kk-admin-identitas-info">
            <p><strong>Nama Admin:</strong> <?php echo esc_html( $admin_dashboard->display_name ); ?></p>
            <p><strong>Username:</strong> <?php echo esc_html( $admin_dashboard->user_login ); ?></p>
            <p><strong>Email:</strong> <?php echo esc_html( $admin_dashboard->user_email ); ?></p>
            <p><strong>Nomor WhatsApp:</strong> <?php echo esc_html( $whatsapp_admin ? $whatsapp_admin : '-' ); ?></p>
            <p><strong>Jenis Kelamin:</strong> <?php echo esc_html( $gender_admin ? $gender_admin : '-' ); ?></p>
        </div>

        <a href="<?php echo esc_url( add_query_arg( 'bagian', 'profil' ) ); ?>" class="kk-admin-link-profil">
            Edit Profil Admin
        </a>
    </section>


            <section class="kk-admin-cards">

                <a class="kk-admin-card"
                   href="<?php echo esc_url( add_query_arg( 'bagian', 'pengguna', get_permalink() ) ); ?>">
                    <span>Pengguna</span>
                    <strong>Kelola Pengguna</strong>
                    <p>Lihat dan kelola akun pengguna website.</p>
                </a>

                <a class="kk-admin-card"
                   href="<?php echo esc_url( add_query_arg( 'bagian', 'produk', get_permalink() ) ); ?>">
                    <span>Produk</span>
                    <strong>Kelola Produk</strong>
                    <p>Atur produk hampers yang tersedia.</p>
                </a>

                <a class="kk-admin-card"
                   href="<?php echo esc_url( add_query_arg( 'bagian', 'pesanan', get_permalink() ) ); ?>">
                    <span>Pesanan</span>
                    <strong>Kelola Pesanan</strong>
                    <p>Periksa pesanan pelanggan.</p>
                </a>

            </section>

        <?php elseif ( $bagian === 'pengguna' ) : ?>

            <section class="kk-admin-welcome">
                <h2>Daftar Pengguna</h2>
                <p>Berikut akun yang terdaftar di website KadoKita.</p>

                <?php
                $daftar_pengguna = get_users(
                    array(
                        'orderby' => 'registered',
                        'order'   => 'DESC'
                    )
                );
                ?>

                <table>
                    <thead>
                        <tr>
                            <th>Nama</th>
                            <th>Email</th>
                            <th>Peran</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ( $daftar_pengguna as $pengguna ) : ?>
                            <tr>
                                <td><?php echo esc_html( $pengguna->display_name ); ?></td>
                                <td><?php echo esc_html( $pengguna->user_email ); ?></td>
                                <td><?php echo esc_html( implode( ', ', $pengguna->roles ) ); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </section>

        <?php elseif ( $bagian === 'produk' ) : ?>

            <section class="kk-admin-welcome">
                <h2>Daftar Produk</h2>
                <p>Berikut daftar produk yang tersedia di toko KadoKita.</p>

                <?php if ( ! function_exists( 'wc_get_products' ) ) : ?>

                    <p>WooCommerce belum aktif. Aktifkan plugin WooCommerce terlebih dahulu.</p>

                <?php else : ?>

                    <?php
                    $daftar_produk = wc_get_products(
                        array(
                            'limit'   => -1,
                            'status'  => array( 'publish', 'draft', 'private' ),
                            'orderby' => 'date',
                            'order'   => 'DESC',
                        )
                    );

                    // Sembunyikan produk yang namanya kosong.
                    $daftar_produk = array_values(
                        array_filter(
                            $daftar_produk,
                            function ( $produk ) {
                                return trim( $produk->get_name() ) !== '';
                            }
                        )
                    );

                    // Pilihan jumlah produk per halaman.
                    $produk_per_halaman = isset( $_GET['jumlah'] )
                        ? absint( $_GET['jumlah'] )
                        : 10;

                    if ( ! in_array( $produk_per_halaman, array( 5, 10, 15 ), true ) ) {
                        $produk_per_halaman = 10;
                    }

                    $total_produk = count( $daftar_produk );
                    $total_halaman = max(
                        1,
                        (int) ceil( $total_produk / $produk_per_halaman )
                    );

                    $halaman_produk = isset( $_GET['halaman_produk'] )
                        ? max( 1, absint( $_GET['halaman_produk'] ) )
                        : 1;

                    $halaman_produk = min( $halaman_produk, $total_halaman );

                    $produk_tampil = array_slice(
                        $daftar_produk,
                        ( $halaman_produk - 1 ) * $produk_per_halaman,
                        $produk_per_halaman
                    );
                    ?>

                    <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px; margin:20px 0;">
                        <p style="margin:0;">
                            Total produk:
                            <strong><?php echo esc_html( $total_produk ); ?></strong>
                        </p>

                        <form method="get"
                              style="display:flex; align-items:center; gap:8px; margin:0;">
                            <input type="hidden" name="bagian" value="produk">

                            <label for="kk-jumlah-produk">Tampilkan:</label>

                            <select id="kk-jumlah-produk" name="jumlah"
                                    onchange="this.form.submit()">
                                <option value="5" <?php selected( $produk_per_halaman, 5 ); ?>>5</option>
                                <option value="10" <?php selected( $produk_per_halaman, 10 ); ?>>10</option>
                                <option value="15" <?php selected( $produk_per_halaman, 15 ); ?>>15</option>
                            </select>

                            <noscript>
                                <button type="submit">Terapkan</button>
                            </noscript>
                        </form>
                    </div>

                    <?php if ( ! empty( $produk_tampil ) ) : ?>

                        <table>
                            <thead>
                                <tr>
                                    <th>No.</th>
                                    <th>Nama Produk</th>
                                    <th>Harga</th>
                                    <th>Stok</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ( $produk_tampil as $indeks => $produk ) : ?>
                                    <tr>
                                        <td>
                                            <?php
                                            echo esc_html(
                                                ( ( $halaman_produk - 1 ) * $produk_per_halaman )
                                                + $indeks + 1
                                            );
                                            ?>
                                        </td>

                                        <td><?php echo esc_html( $produk->get_name() ); ?></td>

                                        <td>
                                            <?php
                                            $harga_produk = $produk->get_price_html();

                                            echo $harga_produk
                                                ? wp_kses_post( $harga_produk )
                                                : 'Belum diatur';
                                            ?>
                                        </td>

                                        <td>
                                            <?php
                                            if ( $produk->managing_stock() ) {
                                                $stok = $produk->get_stock_quantity();

                                                echo esc_html(
                                                    null === $stok
                                                        ? 'Tidak diketahui'
                                                        : (string) $stok
                                                );
                                            } else {
                                                echo esc_html(
                                                    $produk->is_in_stock()
                                                        ? 'Tersedia'
                                                        : 'Habis'
                                                );
                                            }
                                            ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>

                        <?php if ( $total_halaman > 1 ) : ?>

                            <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px; margin-top:20px;">

                                <span>
                                    Halaman <?php echo esc_html( $halaman_produk ); ?>
                                    dari <?php echo esc_html( $total_halaman ); ?>
                                </span>

                                <div style="display:flex; gap:8px;">

                                    <?php if ( $halaman_produk > 1 ) : ?>
                                        <a href="<?php echo esc_url(
                                            add_query_arg(
                                                array(
                                                    'bagian' => 'produk',
                                                    'jumlah' => $produk_per_halaman,
                                                    'halaman_produk' => $halaman_produk - 1,
                                                ),
                                                get_permalink()
                                            )
                                        ); ?>">&laquo; Sebelumnya</a>
                                    <?php endif; ?>

                                    <?php if ( $halaman_produk < $total_halaman ) : ?>
                                        <a href="<?php echo esc_url(
                                            add_query_arg(
                                                array(
                                                    'bagian' => 'produk',
                                                    'jumlah' => $produk_per_halaman,
                                                    'halaman_produk' => $halaman_produk + 1,
                                                ),
                                                get_permalink()
                                            )
                                        ); ?>">Berikutnya &raquo;</a>
                                    <?php endif; ?>

                                </div>
                            </div>

                        <?php endif; ?>

                    <?php else : ?>

                        <p>Belum ada produk yang memiliki nama.</p>

                    <?php endif; ?>

                <?php endif; ?>
            </section>

        
        
<?php elseif ( $bagian === 'profil' ) : ?>

    <?php
    $admin_saat_ini = wp_get_current_user();
    ?>

    <section class="kk-admin-welcome kk-admin-profil">
        <h2>Profil Admin</h2>
        <p>Kelola informasi akun administrator KadoKita.</p>

        <?php if ( isset( $_GET['status_profil'] ) && $_GET['status_profil'] === 'berhasil' ) : ?>
            <p class="kk-profil-sukses">Profil berhasil diperbarui.</p>
        <?php endif; ?>

        <h3>Informasi Akun</h3>

        <form method="post" enctype="multipart/form-data">
            <?php wp_nonce_field( 'kk_simpan_profil', 'kk_nonce_profil' ); ?>

            <div class="kk-profil-form">
                
<?php
$url_foto_profil = get_user_meta(
    $admin_saat_ini->ID,
    'kk_foto_profil',
    true
);
?>

<label for="kk_foto_profil">Foto Profil</label>


<?php if ( $url_foto_profil ) : ?>
    <img
        src="<?php echo esc_url( $url_foto_profil ); ?>"
        alt="Foto profil admin"
        class="kk-foto-profil-preview"
    >

    <label class="kk-hapus-foto">
        <input
            type="checkbox"
            name="kk_hapus_foto_profil"
            value="1"
        >
        Hapus foto profil
    </label>
<?php else : ?>
    <?php echo get_avatar( $admin_saat_ini->ID, 100 ); ?>
<?php endif; ?>


<input
    type="file"
    id="kk_foto_profil"
    name="kk_foto_profil"
    accept="image/jpeg,image/png,image/webp"
>
<p>Format JPG, PNG, atau WebP. Maksimal 2 MB.</p>

                
        <label for="kk_nama_admin">Nama Admin</label>
            <input
                type="text"
                id="kk_nama_admin"
                name="kk_nama_admin"
                value="<?php echo esc_attr( $admin_saat_ini->display_name ); ?>"
                required
            >

        <label for="kk_nomor_wa">Nomor WhatsApp</label>
            <input
                type="tel"
                id="kk_nomor_wa"
                name="kk_nomor_wa"
                value="<?php echo esc_attr( get_user_meta( $admin_saat_ini->ID, 'kk_nomor_wa', true ) ); ?>"
                placeholder="Contoh: 081234567890"
            >

        <label for="kk_jenis_kelamin">Jenis Kelamin</label>
            <select id="kk_jenis_kelamin" name="kk_jenis_kelamin">
            <option value="">Pilih jenis kelamin</option>
            <option value="Laki-laki" <?php selected( get_user_meta( $admin_saat_ini->ID, 'kk_jenis_kelamin', true ), 'Laki-laki' ); ?>>Laki-laki</option>
            <option value="Perempuan" <?php selected( get_user_meta( $admin_saat_ini->ID, 'kk_jenis_kelamin', true ), 'Perempuan' ); ?>>Perempuan</option>
        </select>


                <label for="kk_email_admin">Email Admin</label>
                <input
                    type="email"
                    id="kk_email_admin"
                    name="kk_email_admin"
                    value="<?php echo esc_attr( $admin_saat_ini->user_email ); ?>"
                    required
                >

                <label>Username</label>
                <input
                    type="text"
                    value="<?php echo esc_attr( $admin_saat_ini->user_login ); ?>"
                    readonly
                >

                <button type="submit" name="kk_simpan_profil" value="1">
                    Simpan Profil
                </button>
            </div>
        </form>
    </section>

<?php elseif ( $bagian === 'kategori' ) : ?>


            <section class="kk-admin-welcome kk-admin-kategori">
                <h2>Kategori Produk</h2>
                <p>Kelola kategori produk KadoKita melalui bagian ini.</p>

                <?php
                    $kategori_produk = get_terms(
                    array(
                        'taxonomy'   => 'product_cat',
                        'hide_empty' => false,
                        'exclude'    => array(
                        get_option( 'default_product_cat' )
                    ),
                    )
                );
                ?>

                <?php if ( isset( $_GET['status_kategori'] ) ) : ?>
                    <p>
                        <?php
                        $status = sanitize_key(
                            wp_unslash( $_GET['status_kategori'] )
                        );

                        $pesan = array(
                            'berhasil_tambah' => 'Kategori berhasil ditambahkan.',
                            'berhasil_edit'   => 'Kategori berhasil diubah.',
                            'berhasil_hapus'  => 'Kategori berhasil dihapus.',
                            'gagal'            => 'Kategori gagal diproses.',
                        );

                        echo esc_html( $pesan[ $status ] ?? '' );
                        ?>
                    </p>
                <?php endif; ?>

                <h3>Tambah Kategori</h3>

                <form method="post">
                    <?php wp_nonce_field( 'kk_kelola_kategori', 'kk_nonce_kategori' ); ?>
                    <input type="hidden" name="kk_aksi_kategori" value="tambah">

                    <input
                        type="text"
                        name="nama_kategori"
                        placeholder="Masukkan nama kategori"
                        required
                    >

                    <button type="submit">Tambah Kategori</button>
                </form>

                <h3>Daftar Kategori</h3>

                <?php if ( ! is_wp_error( $kategori_produk ) && ! empty( $kategori_produk ) ) : ?>

                    <table>
                        <thead>
                            <tr>
                                <th>Nama Kategori</th>
                                <th>Jumlah Produk</th>
                                <th>Edit Nama</th>
                                <th>Hapus</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ( $kategori_produk as $kategori ) : ?>
                                <tr>
                                    <td><?php echo esc_html( $kategori->name ); ?></td>
                                    <td><?php echo esc_html( $kategori->count ); ?></td>

                                    <td>
                                        <form method="post">
                                            <?php wp_nonce_field( 'kk_kelola_kategori', 'kk_nonce_kategori' ); ?>
                                            <input type="hidden" name="kk_aksi_kategori" value="edit">
                                            <input type="hidden" name="id_kategori" value="<?php echo esc_attr( $kategori->term_id ); ?>">
                                            <input type="text" name="nama_kategori" value="<?php echo esc_attr( $kategori->name ); ?>" required>
                                            <button type="submit">Simpan</button>
                                        </form>
                                    </td>

                                    <td>
                                        <form method="post" onsubmit="return confirm('Yakin ingin menghapus kategori ini?');">
                                            <?php wp_nonce_field( 'kk_kelola_kategori', 'kk_nonce_kategori' ); ?>
                                            <input type="hidden" name="kk_aksi_kategori" value="hapus">
                                            <input type="hidden" name="id_kategori" value="<?php echo esc_attr( $kategori->term_id ); ?>">
                                            <button type="submit">Hapus</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>

                <?php else : ?>
                    <p>Belum ada kategori produk.</p>
                <?php endif; ?>
            
            </section>

        <?php elseif ( $bagian === 'pengaturan' ) : ?>

            <section class="kk-admin-welcome kk-admin-pengaturan">
                <h2>Pengaturan Web</h2>
                
<?php if ( isset( $_GET['status_pengaturan'] ) && $_GET['status_pengaturan'] === 'berhasil' ) : ?>
    <p class="kk-pengaturan-sukses">
        Pengaturan website berhasil disimpan.
    </p>
<?php endif; ?>

                <p>Kelola informasi dasar website KadoKita melalui bagian ini.</p>

                <h3>Informasi Website</h3>

                <form method="post">
                    <?php wp_nonce_field( 'kk_simpan_pengaturan', 'kk_nonce_pengaturan' ); ?>

                    <div class="kk-pengaturan-form">
                        <label for="kk_nama_toko">Nama Toko</label>
                        <input
                            type="text"
                            id="kk_nama_toko"
                            name="kk_nama_toko"
                            value="<?php echo esc_attr( get_option( 'kk_nama_toko', 'KadoKita' ) ); ?>"
                            required
                        >

                        <label for="kk_deskripsi_toko">Deskripsi Toko</label>
                        <textarea
                            id="kk_deskripsi_toko"
                            name="kk_deskripsi_toko"
                            rows="4"
                        ><?php echo esc_textarea( get_option( 'kk_deskripsi_toko', 'Temukan hadiah spesial untuk orang tersayang.' ) ); ?></textarea>

                        <label for="kk_email_toko">Email Kontak</label>
                        <input
                            type="email"
                            id="kk_email_toko"
                            name="kk_email_toko"
                            value="<?php echo esc_attr( get_option( 'kk_email_toko', get_option( 'admin_email' ) ) ); ?>"
                        >

                        <label for="kk_telepon_toko">Nomor WhatsApp</label>
                        <input
                            type="text"
                            id="kk_telepon_toko"
                            name="kk_telepon_toko"
                            value="<?php echo esc_attr( get_option( 'kk_telepon_toko', '' ) ); ?>"
                            placeholder="Contoh: 081234567890"
                        >

                        <button type="submit" name="kk_simpan_pengaturan" value="1">
                            Simpan Pengaturan
                        </button>
                        
                    </div>
                </form>
            </section>

        <?php elseif ( $bagian === 'pesanan' ) : ?>


            <section class="kk-admin-welcome kk-admin-pesanan">
                <h2>Daftar Pesanan</h2>
                <p>Pantau dan kelola pesanan pelanggan KadoKita.</p>

                <?php if ( ! function_exists( 'wc_get_orders' ) ) : ?>

                    <p>WooCommerce belum aktif.</p>

                <?php else : ?>

                    <?php
                    $semua_pesanan = wc_get_orders(
                        array(
                            'limit'   => -1,
                            'orderby' => 'date',
                            'order'   => 'DESC',
                            'return'  => 'objects',
                        )
                    );

                    $total_pesanan = count( $semua_pesanan );
                    $menunggu = 0;
                    $diproses = 0;
                    $selesai = 0;

                    foreach ( $semua_pesanan as $pesanan ) {
                        $status = $pesanan->get_status();

                        if ( in_array( $status, array( 'pending', 'on-hold' ), true ) ) {
                            $menunggu++;
                        } elseif ( in_array( $status, array( 'processing', 'packing' ), true ) ) {
                            $diproses++;
                        } elseif ( 'completed' === $status ) {
                            $selesai++;
                        }
                    }
                    ?>

                    <div class="kk-pesanan-ringkasan">
                        <div class="kk-pesanan-kartu">
                            <span>Total Pesanan</span>
                            <strong><?php echo esc_html( $total_pesanan ); ?></strong>
                        </div>

                        <div class="kk-pesanan-kartu">
                            <span>Menunggu</span>
                            <strong><?php echo esc_html( $menunggu ); ?></strong>
                        </div>

                        <div class="kk-pesanan-kartu">
                            <span>Sedang Diproses</span>
                            <strong><?php echo esc_html( $diproses ); ?></strong>
                        </div>

                        <div class="kk-pesanan-kartu">
                            <span>Selesai</span>
                            <strong><?php echo esc_html( $selesai ); ?></strong>
                        </div>
                    </div>

                    
<h3>Semua Pesanan</h3>

<div class="kk-pesanan-filter">
    <input
        type="search"
        id="kkCariPesanan"
        placeholder="Cari nomor atau nama pelanggan..."
    >

    <select id="kkFilterStatus">
        <option value="">Semua Status</option>
        <option value="pending">Menunggu pembayaran</option>
        <option value="on-hold">Ditahan</option>
        <option value="processing">Sedang diproses</option>
        <option value="completed">Selesai</option>
        <option value="cancelled">Dibatalkan</option>
        <option value="refunded">Dikembalikan</option>
        <option value="failed">Gagal</option>
    </select>
</div>


                    <?php if ( ! empty( $semua_pesanan ) ) : ?>

                        <div class="kk-pesanan-tabel">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Nomor</th>
                                        <th>Pelanggan</th>
                                        <th>Tanggal</th>
                                        <th>Total</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ( $semua_pesanan as $pesanan ) : ?>
    <?php
    $nama = trim( $pesanan->get_formatted_billing_full_name() );
    $nomor = $pesanan->get_order_number();
    $status_pesanan = $pesanan->get_status();
    ?>
    <tr
        data-cari="<?php echo esc_attr( strtolower( $nomor . ' ' . $nama ) ); ?>"
        data-status="<?php echo esc_attr( $status_pesanan ); ?>"
    >
                                            <td>
                                                #<?php echo esc_html( $pesanan->get_order_number() ); ?>
                                            </td>
                                            <td>
                                                <?php
                                                $nama = trim(
                                                    $pesanan->get_formatted_billing_full_name()
                                                );
                                                echo esc_html( $nama ?: 'Pelanggan' );
                                                ?>
                                            </td>
                                            <td>
                                                <?php
                                                $tanggal = $pesanan->get_date_created();
                                                echo esc_html(
                                                    $tanggal
                                                        ? $tanggal->date_i18n( 'd M Y' )
                                                        : '—'
                                                );
                                                ?>
                                            </td>
                                            <td>
                                                <?php echo wp_kses_post( $pesanan->get_formatted_order_total() ); ?>
                                            </td>
                                            <td>
                                                <?php echo esc_html( wc_get_order_status_name( $pesanan->get_status() ) ); ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                    <?php else : ?>
                        <p>Belum ada pesanan. Pesanan pelanggan akan muncul di sini.</p>
                    <?php endif; ?>

                <?php endif; ?>


<script>
document.addEventListener('DOMContentLoaded', function () {
    const pencarian = document.getElementById('kkCariPesanan');
    const filterStatus = document.getElementById('kkFilterStatus');

    if (!pencarian || !filterStatus) return;

    function filterPesanan() {
        const kata = pencarian.value.toLowerCase().trim();
        const status = filterStatus.value;

        document.querySelectorAll('.kk-pesanan-tabel tbody tr').forEach(function (baris) {
            const teks = baris.dataset.cari || '';
            const statusBaris = baris.dataset.status || '';

            baris.style.display =
                teks.includes(kata) &&
                (!status || statusBaris === status)
                    ? ''
                    : 'none';
        });
    }

    pencarian.addEventListener('input', filterPesanan);
    filterStatus.addEventListener('change', filterPesanan);
});
</script>

                
            </section>



        <?php else : ?>

            <section class="kk-admin-welcome">
                <h2><?php echo esc_html( $judul_menu[$bagian] ); ?></h2>
                <p>
                    Bagian ini sudah disiapkan. Fitur pengelolaan
                    <?php echo esc_html( strtolower( $judul_menu[$bagian] ) ); ?>
                    akan ditambahkan pada langkah berikutnya.
                </p>
            </section>


            <section class="kk-admin-welcome">
                <h2><?php echo esc_html( $judul_menu[$bagian] ); ?></h2>
                <p>
                    Bagian ini sudah disiapkan. Fitur pengelolaan
                    <?php echo esc_html( strtolower( $judul_menu[$bagian] ) ); ?>
                    akan ditambahkan pada langkah berikutnya.
                </p>
            </section>

        <?php endif; ?>

    </main>

</div>

<?php get_footer(); ?>
