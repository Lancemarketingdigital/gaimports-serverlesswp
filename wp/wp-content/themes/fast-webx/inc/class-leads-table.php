<?php
/**
 * Tabela de Listagem de Leads (Painel Admin)
 *
 * @package Fast_WebX
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WP_List_Table' ) ) {
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class FastWebX_Leads_Table extends WP_List_Table {

    public function __construct() {
        parent::__construct( array(
            'singular' => 'lead',
            'plural'   => 'leads',
            'ajax'     => false
        ) );
    }

    public function get_columns() {
        return array(
            'cb'         => '<input type="checkbox" />',
            'id'         => 'ID',
            'email'      => 'E-mail',
            'whatsapp'   => 'WhatsApp',
            'created_at' => 'Data de Cadastro',
            'ip_address' => 'IP'
        );
    }

    public function get_sortable_columns() {
        return array(
            'id'         => array( 'id', false ),
            'email'      => array( 'email', false ),
            'created_at' => array( 'created_at', true )
        );
    }

    protected function column_cb( $item ) {
        return sprintf(
            '<input type="checkbox" name="lead[]" value="%s" />',
            esc_attr( $item['id'] )
        );
    }

    protected function column_default( $item, $column_name ) {
        switch ( $column_name ) {
            case 'id':
            case 'email':
            case 'whatsapp':
            case 'ip_address':
                return esc_html( $item[ $column_name ] );
            case 'created_at':
                return date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime( $item[ $column_name ] ) );
            default:
                return print_r( $item, true );
        }
    }

    public function get_bulk_actions() {
        $actions = array(
            'delete' => 'Excluir'
        );
        return $actions;
    }

    public function process_bulk_action() {
        if ( 'delete' === $this->current_action() ) {
            check_admin_referer( 'bulk-' . $this->_args['plural'] );

            $leads_to_delete = isset( $_GET['lead'] ) ? $_GET['lead'] : array();

            if ( ! empty( $leads_to_delete ) ) {
                global $wpdb;
                $table_name = $wpdb->prefix . 'fastwebx_leads';

                $ids = array_map( 'intval', $leads_to_delete );
                $ids_string = implode( ',', $ids );

                $wpdb->query( "DELETE FROM {$table_name} WHERE id IN($ids_string)" );
            }
        }
    }

    public function prepare_items() {
        $this->process_bulk_action();

        global $wpdb;
        $table_name = $wpdb->prefix . 'fastwebx_leads';

        // Paginação
        $per_page = 20;
        $current_page = $this->get_pagenum();
        
        // Ordenação — validar contra lista de colunas permitidas para evitar SQL injection
        $allowed_orderby = array( 'id', 'email', 'whatsapp', 'created_at', 'ip_address' );
        $allowed_order   = array( 'asc', 'desc' );

        $orderby_raw = isset( $_GET['orderby'] ) ? sanitize_text_field( $_GET['orderby'] ) : 'created_at';
        $order_raw   = isset( $_GET['order'] )   ? strtolower( sanitize_text_field( $_GET['order'] ) ) : 'desc';

        $orderby = in_array( $orderby_raw, $allowed_orderby, true ) ? $orderby_raw : 'created_at';
        $order   = in_array( $order_raw, $allowed_order, true )     ? $order_raw   : 'desc';

        $total_items = (int) $wpdb->get_var( "SELECT COUNT(id) FROM `{$table_name}`" );

        $offset = ( $current_page - 1 ) * $per_page;

        // Interpolação segura: orderby e order foram validados acima via whitelist
        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        $this->items = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM `{$table_name}` ORDER BY `{$orderby}` {$order} LIMIT %d OFFSET %d",
                $per_page,
                $offset
            ),
            ARRAY_A
        );

        $this->set_pagination_args( array(
            'total_items' => $total_items,
            'per_page'    => $per_page,
            'total_pages' => ceil( $total_items / $per_page )
        ) );

        $this->_column_headers = array( $this->get_columns(), array(), $this->get_sortable_columns() );
    }
}

/**
 * Registro da Página no Admin (Leads e Suporte)
 */
function fwx_register_admin_submenus() {
    // Menu de Leads
    add_submenu_page(
        'fast-webx',
        'Lista de Leads Capturados',
        'Leads',
        'manage_options',
        'fast-webx-leads',
        'fwx_render_leads_page'
    );

    // Menu de Suporte (Abaixo de Leads)
    add_submenu_page(
        'fast-webx',
        'Suporte e Documentação',
        'Suporte',
        'manage_options',
        'fast-webx-support',
        'fwx_render_support_page'
    );
}
add_action( 'admin_menu', 'fwx_register_admin_submenus' );

/**
 * Renderização da Página de Suporte
 */
function fwx_render_support_page() {
    $img_path = get_template_directory_uri() . '/assets/img/banners/';
    ?>
    <div class="wrap" style="max-width: 1333px !important; width: 100% !important; margin: 0; padding-right: 20px;">
        <h1>Suporte e Documentação</h1>
        
        <div style="display: grid; grid-template-columns: 1fr 350px; gap: 20px; align-items: flex-start; margin-top: 20px; width: 80%;">
            
            <!-- Conteúdo Principal -->
            <div class="card" style="max-width: 1200px !important; width: 100% !important; padding: 25px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); border: none; margin-top: 0;">
                <h2 style="color: #2271b1; margin-top: 0;">Precisa de ajuda?</h2>
                <p style="font-size: 15px; line-height: 1.6; color: #50575e;">Se você tiver dúvidas sobre a configuração ou encontrar algum comportamento inesperado, utilize nossos canais oficiais:</p>
                
                <div style="background: #f6f7f7; padding: 20px; border-radius: 8px; margin: 20px 0;">
                    <table class="form-table" style="margin: 0;">
                        <tr>
                            <th style="width: 200px; padding: 10px 0;"><strong>E-mail de Suporte:</strong></th>
                            <td style="padding: 10px 0;"><code>suporte@mesaquemota.com.br</code></td>
                        </tr>
                        <tr>
                            <th style="width: 200px; padding: 10px 0;"><strong>Suporte WhatsApp:</strong></th>
                            <td style="padding: 10px 0;">Disponível no Guia Completo (Horário: 09h às 18h — Seg. a Sex.)</td>
                        </tr>
                        <tr>
                            <th style="width: 200px; padding: 20px 0 10px;"><strong>Documentação:</strong></th>
                            <td style="padding: 20px 0 10px;">
                                <a href="https://fwx.academiawpx.com/docs/" target="_blank" class="button button-primary button-large">Abrir Guia Completo</a>
                                <p class="description" style="margin-top: 10px;">Acesse o manual de funcionalidades, SEO e Performance (v<?php echo FWX_VERSION; ?>).</p>
                            </td>
                        </tr>
                    </table>
                </div>

                <hr style="margin: 30px 0; border: 0; border-top: 1px solid #dcdcde;">

                <h3 style="margin-top: 0;">Dicas de Ouro:</h3>
                <ul style="list-style: disc; margin-left: 20px; line-height: 1.8; color: #50575e;">
                    <li>Mantenha o tema sempre atualizado para garantir a segurança e performance.</li>
                    <li>Utilize o motor de <strong>Otimização de Mídias</strong> para garantir o PageSpeed 100/100.</li>
                    <li>Consulte a aba <strong>Core Clean</strong> no painel principal para remover lixos do WordPress.</li>
                    <li><strong>Imagens Ideais:</strong> Sempre utilize formato **WebP** e dimensões mínimas de **1280x853px**. O tema fará o recorte automático para a proporção perfeita, mas uma base maior garante nitidez total.</li>
                </ul>
            </div>

            <!-- Sidebar de Ofertas -->
            <div style="display: flex; flex-direction: column; gap: 20px;">
                
                <!-- Card 1: Curso AutoBlog -->
                <div style="background: #fff; padding: 15px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); transition: transform 0.2s;" onmouseover="this.style.transform='translateY(-5px)'" onmouseout="this.style.transform='translateY(0)'">
                    <a href="https://urlaki.com/autoblog" target="_blank" style="text-decoration: none; color: inherit;">
                        <img src="<?php echo $img_path; ?>banner-autoblog-4x3.jpg" style="width: 100%; border-radius: 8px; margin-bottom: 10px; display: block;" alt="Curso AutoBlog">
                        <h4 style="margin: 0 0 5px 0; font-size: 14px; font-weight: 600;">Curso AutoBlog WPX-N8N</h4>
                        <p style="margin: 5px 0; font-size: 13px; color: #1d2327;">Cupom: <strong style="color: #dd3333;">FWX50OFF</strong> (50% OFF)</p>
                        <p style="margin: 0; font-size: 12px; color: #666; line-height: 1.4;">Aprenda a criar sites automáticos de alta performance com N8N.</p>
                    </a>
                </div>

                <!-- Card 2: GeraArtigoAi Pro -->
                <div style="background: #fff; padding: 15px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); transition: transform 0.2s;" onmouseover="this.style.transform='translateY(-5px)'" onmouseout="this.style.transform='translateY(0)'">
                    <a href="https://urlaki.com/plugin-gaipro" target="_blank" style="text-decoration: none; color: inherit;">
                        <img src="<?php echo $img_path; ?>GeraArtigoIAPro-V.webp" style="width: 100%; border-radius: 8px; margin-bottom: 10px; display: block;" alt="GeraArtigoAi Pro">
                        <h4 style="margin: 0 0 5px 0; font-size: 14px; font-weight: 600;">Plugin GeraArtigoAi Pro</h4>
                        <p style="margin: 5px 0; font-size: 13px; color: #1d2327;">Cupom: <strong style="color: #dd3333;">FWX50OFF</strong> (50% OFF)</p>
                        <p style="margin: 0; font-size: 12px; color: #666; line-height: 1.4;">Crie conteúdos épicos com Inteligência Artificial em segundos.</p>
                    </a>
                </div>

                <!-- Card 3: Hostinger -->
                <div style="background: #fff; padding: 15px; border-radius: 12px; box-shadow: 0 4px 15px rgba(103, 58, 183, 0.15); border: 2px solid #673ab7; transition: transform 0.2s;" onmouseover="this.style.transform='translateY(-5px)'" onmouseout="this.style.transform='translateY(0)'">
                    <a href="https://urlaki.com/hostngr-fwx" target="_blank" style="text-decoration: none; color: inherit;">
                        <img src="<?php echo $img_path; ?>banner-hostinger-4x3.jpg" style="width: 100%; border-radius: 8px; margin-bottom: 10px; display: block;" alt="Hostinger">
                        <h4 style="margin: 0 0 5px 0; font-size: 14px; font-weight: 600; color: #673ab7;">Hospedagem Hostinger</h4>
                        <p style="margin: 5px 0; font-size: 13px; color: #1d2327;">Cupom: <strong style="color: #dd3333;">MESAQUEMOTA</strong></p>
                        <p style="margin: 0; font-size: 12px; color: #666; line-height: 1.4;">Ganhe 10% OFF extra na melhor hospedagem para WordPress.</p>
                    </a>
                </div>

            </div>
        </div>
    </div>
    <?php
}


/**
 * Lógica do Display e Escuta de Download de CSV
 */
function fwx_handle_csv_export() {
    if ( isset( $_GET['page'] ) && $_GET['page'] === 'fast-webx-leads' && isset( $_GET['fwx_export_csv'] ) && $_GET['fwx_export_csv'] == '1' && current_user_can('manage_options') ) {
        check_admin_referer( 'fwx_export_leads_csv' );
        fwx_export_leads_to_csv();
        exit;
    }
}
add_action( 'admin_init', 'fwx_handle_csv_export' );

function fwx_render_leads_page() {
    $leads_table = new FastWebX_Leads_Table();
    $leads_table->prepare_items();
    ?>
    <div class="wrap">
        <h1 class="wp-heading-inline">Gestão de Leads</h1>
        <a href="<?php echo wp_nonce_url( admin_url('admin.php?page=fast-webx-leads&fwx_export_csv=1'), 'fwx_export_leads_csv' ); ?>" class="page-title-action">Exportar CSV Completo</a>
        <hr class="wp-header-end">
        
        <form method="get">
            <input type="hidden" name="page" value="fast-webx-leads" />
            <?php $leads_table->display(); ?>
        </form>
    </div>
    <?php
}

function fwx_export_leads_to_csv() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'fastwebx_leads';
    
    $leads = $wpdb->get_results( "SELECT email, whatsapp, created_at, ip_address FROM $table_name ORDER BY id DESC", ARRAY_A );

    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="fast-webx-leads-' . date('Y-m-d') . '.csv"');
    
    $output = fopen('php://output', 'w');
    fputcsv($output, array('E-mail', 'WhatsApp', 'Data de Cadastro', 'IP Mapeado'));
    
    if ( $leads ) {
        foreach ( $leads as $lead ) {
            fputcsv($output, $lead);
        }
    }
    fclose($output);
}
