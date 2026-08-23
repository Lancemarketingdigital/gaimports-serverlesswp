<?php
/**
 * Fast WebX - Dicionário e Sistema de Tradução Nativa Simplificada
 *
 * @package Fast_WebX
 * @since   3.1.3
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Retorna o dicionário de traduções do tema para os idiomas suportados.
 * 
 * @return array
 */
function fwx_get_theme_translations() {
    return array(
        // Português do Brasil (Padrão)
        'pt_BR' => array(
            // TOC (Sumário)
            'toc_title'                 => 'Neste artigo:',
            'toc_toggle_hide'           => 'Esconder sumário',
            'toc_toggle_show'           => 'Mostrar sumário',
            'toc_toggle_title'          => 'Mostrar/Esconder',

            // Tempo de Leitura
            'reading_time_less_than_1'  => 'Menos de 1 min de leitura',
            'reading_time_suffix'       => 'min de leitura',

            // 404
            '404_title'                 => 'Opa! Página não encontrada.',
            '404_text'                  => 'Parece que o link que você seguiu não existe mais ou foi movido. Que tal tentar uma busca ou conferir nossos últimos artigos?',
            '404_btn_home'              => 'Voltar para Home',
            '404_btn_search'            => 'Pesquisar Site',
            '404_recent_title'          => 'Artigos que podem te interessar:',

            // Footer
            'footer_about'              => 'Sobre',
            'footer_links'              => 'Links',
            'footer_site'               => 'Site',
            'footer_home'               => 'Início',
            'footer_setup_menu'         => 'Configurar Menu Rodapé',
            'footer_follow'             => 'Acompanhe',
            'footer_close'              => 'Fechar',
            'footer_back_to_top'        => 'Voltar ao topo',
            'footer_text_default'       => '© [ano] [nome_site]. Todos os direitos reservados.',
            'footer_aux_text_default'   => 'Construído para leitores que buscam sinal, não ruído.',
            'exit_intent_title_default' => 'Espere! Não vá embora ainda...',
            'exit_intent_desc_default'  => 'Temos um presente especial para você antes de sair.',

            // Header
            'header_open_menu'          => 'Abrir Menu',
            'header_close_menu'         => 'Fechar Menu',
            'header_open_search'        => 'Abrir Busca',
            'header_toggle_dark_mode'   => 'Alternar Modo Escuro',
            'header_skip_link'          => 'Pular para o conteúdo principal',
            'header_close_search'       => 'Fechar Busca',
            'header_search_placeholder' => 'O que você está procurando?',
            'header_search_submit'      => 'Pesquisar',
            'header_search_help'        => 'Pressione Enter para buscar apenas em nossos artigos.',
            'header_cta_text_default'   => 'Fale Conosco',

            // Index/Archive/Search
            'archive_search_label'      => 'Busca',
            'archive_search_results'    => 'Resultados para: %s',
            'archive_prefix_category'   => 'Categoria',
            'archive_prefix_tag'        => 'Tag',
            'archive_prefix_archive'    => 'Arquivo',
            'archive_posts_label'       => 'Posts',
            'archive_load_more'         => 'Ver Mais Posts',
            'archive_loading'           => 'Carregando...',
            'archive_error'             => 'Erro. Tentar Novamente',
            'archive_no_content'        => 'Nenhum conteúdo encontrado.',

            // Single Post & Core
            'single_author_prefix'      => 'Por',
            'single_pages'              => 'Páginas:',
            'single_tags'               => 'Tags: ',
            'single_related_title'      => 'Você também pode gostar',
            'single_sidebar_recent'     => 'Posts Recentes',
            'breadcrumbs_nav_label'     => 'Caminho de navegação',
            'categories_title'          => 'Categorias',
            'share_title'               => 'Compartilhe:',
            'share_whatsapp_label'      => 'Compartilhar no WhatsApp',
            'share_facebook_label'      => 'Compartilhar no Facebook',
            'share_twitter_label'      => 'Compartilhar no X (Twitter)',

            // LGPD Banner
            'lgpd_policy_btn'           => 'Política de Privacidade',
            'lgpd_accept_btn'           => 'Entendi e Aceito',
            'lgpd_text_default'         => 'Nós usamos cookies e dados de navegação para melhorar sua experiência. Ao continuar, você concorda com nossa Política de Privacidade.',

            // Lead Capture
            'lead_error_spam'           => 'Requisição bloqueada por segurança (Spam detectado).',
            'lead_error_whatsapp_req'   => 'O campo de WhatsApp é obrigatório.',
            'lead_error_email_req'      => 'E-mail inválido ou obrigatório.',
            'lead_error_whatsapp_invalid' => 'Número de WhatsApp inválido.',
            'lead_error_lgpd_req'       => 'É obrigatório aceitar os termos da LGPD.',
            'lead_success_msg'          => 'Cadastro realizado com sucesso!',
            'lead_error_duplicate'      => 'Este e-mail já está em nossa lista ou ocorreu um erro.',
            'lead_error_server'         => 'Erro no servidor. Por favor, recarregue a página e tente novamente.',
            'lead_error_generic'        => 'Ocorreu um erro.',
            'lead_error_server_try'     => 'Erro no servidor, tente novamente.',
            'lead_error_temp_mail'      => 'E-mail de serviço temporário não permitido. Use seu e-mail real.',
            'lead_error_invalid_format' => 'E-mail com formato inválido. Tente um endereço real.',
            'lead_error_no_mx'          => 'O domínio deste e-mail não existe ou não aceita e-mails.',
            'lead_placeholder_email'    => 'Digite seu melhor e-mail',
            'lead_btn_submit'           => 'Quero Receber',
            'lead_redirecting'           => 'Redirecionando, aguarde...',
            'lead_sending'              => 'Enviando...',
            'lead_form_title_default'   => 'Cadastre-se na nossa Newsletter',
            'lead_form_desc_default'    => 'Receba as melhores dicas e atualizações semanais gratuitamente.',
            'lead_form_lgpd_default'    => 'Eu concordo com a Política de Privacidade e aceito receber comunicações (LGPD).',

            // Author Box & Bio
            'author_bio_fallback'       => 'Autor em %s',
            'author_profile_btn'        => 'Meu Perfil Completo',
            'author_site_link'          => 'Site do Autor',
            'lp_about_badge'            => 'Especialista',
            'lp_service_read_more'      => 'Saiba Mais',
            'lp_hero_btn_default'       => 'Começar Agora',
            
            // Loja Catálogo & Relacionados
            'widget_recent_products'    => 'Produtos Recentes',
            'loja_catalog_badge_cat'    => 'Categoria',
            'loja_catalog_badge_store'  => 'Catálogo',
            'loja_related_title'        => 'Você também pode gostar',
            'loja_filter_category'      => 'Filtrar por categoria',
            'loja_filter_all'           => 'Todos',
            'loja_nav_prev'             => 'Anterior',
            'loja_nav_next'             => 'Próxima',
            'loja_nav_catalog'          => 'Navegação do catálogo',
            'loja_empty_category'       => 'Nenhum produto encontrado nesta categoria.',
            
            // Formulário de Contato
            'contact_label_name'         => 'Nome Completo',
            'contact_placeholder_name'   => 'Seu nome',
            'contact_label_email'        => 'E-mail',
            'contact_placeholder_email'  => 'seu@email.com',
            'contact_label_subject'      => 'Assunto',
            'contact_placeholder_subject' => 'Do que se trata?',
            'contact_label_message'      => 'Mensagem',
            'contact_placeholder_message' => 'Escreva sua mensagem aqui...',
            'contact_btn_submit'         => 'Enviar Mensagem',
            'contact_btn_sending'        => 'Enviando...',
            'contact_msg_conn_error'     => 'Erro de conexão. Tente mais tarde.',
            'contact_err_spam'           => 'Requisição bloqueada por segurança (Spam detectado).',
            'contact_err_disabled'       => 'O formulário de contato está desativado no momento.',
            'contact_err_required'       => 'Por favor, preencha todos os campos obrigatórios corretamente.',
            'contact_success_msg'        => 'Sua mensagem foi enviada com sucesso! Entraremos em contato em breve.',
            'contact_err_mail'           => 'Ocorreu um erro ao enviar e-mail. Tente novamente mais tarde.',
        ),

        // Inglês (Fase 2)
        'en_US' => array(
            // TOC (Sumário)
            'toc_title'                 => 'In this article:',
            'toc_toggle_hide'           => 'Hide table of contents',
            'toc_toggle_show'           => 'Show table of contents',
            'toc_toggle_title'          => 'Show/Hide',

            // Tempo de Leitura
            'reading_time_less_than_1'  => 'Less than 1 min read',
            'reading_time_suffix'       => 'min read',

            // 404
            '404_title'                 => 'Oops! Page not found.',
            '404_text'                  => 'It looks like the link you followed no longer exists or has been moved. How about searching or checking out our latest articles?',
            '404_btn_home'              => 'Back to Home',
            '404_btn_search'            => 'Search Site',
            '404_recent_title'          => 'Articles that might interest you:',

            // Footer
            'footer_about'              => 'About',
            'footer_links'              => 'Links',
            'footer_site'               => 'Site',
            'footer_home'               => 'Home',
            'footer_setup_menu'         => 'Configure Footer Menu',
            'footer_follow'             => 'Follow',
            'footer_close'              => 'Close',
            'footer_back_to_top'        => 'Back to top',
            'footer_text_default'       => '© [ano] [nome_site]. All rights reserved.',
            'footer_aux_text_default'   => 'Built for readers seeking signal, not noise.',
            'exit_intent_title_default' => 'Wait! Don\'t leave yet...',
            'exit_intent_desc_default'  => 'We have a special gift for you before you go.',

            // Header
            'header_open_menu'          => 'Open Menu',
            'header_close_menu'         => 'Close Menu',
            'header_open_search'        => 'Open Search',
            'header_toggle_dark_mode'   => 'Toggle Dark Mode',
            'header_skip_link'          => 'Skip to main content',
            'header_close_search'       => 'Close Search',
            'header_search_placeholder' => 'What are you looking for?',
            'header_search_submit'      => 'Search',
            'header_search_help'        => 'Press Enter to search only in our articles.',
            'header_cta_text_default'   => 'Contact Us',

            // Index/Archive/Search
            'archive_search_label'      => 'Search',
            'archive_search_results'    => 'Results for: %s',
            'archive_prefix_category'   => 'Category',
            'archive_prefix_tag'        => 'Tag',
            'archive_prefix_archive'    => 'Archive',
            'archive_posts_label'       => 'Posts',
            'archive_load_more'         => 'Load More Posts',
            'archive_loading'           => 'Loading...',
            'archive_error'             => 'Error. Try Again',
            'archive_no_content'        => 'No content found.',

            // Single Post & Core
            'single_author_prefix'      => 'By',
            'single_pages'              => 'Pages:',
            'single_tags'               => 'Tags: ',
            'single_related_title'      => 'You may also like',
            'single_sidebar_recent'     => 'Recent Posts',
            'breadcrumbs_nav_label'     => 'Navigation path',
            'categories_title'          => 'Categories',
            'share_title'               => 'Share:',
            'share_whatsapp_label'      => 'Share on WhatsApp',
            'share_facebook_label'      => 'Share on Facebook',
            'share_twitter_label'      => 'Share on X (Twitter)',

            // LGPD Banner
            'lgpd_policy_btn'           => 'Privacy Policy',
            'lgpd_accept_btn'           => 'I Understand and Accept',
            'lgpd_text_default'         => 'We use cookies and navigation data to improve your experience. By continuing, you agree to our Privacy Policy.',

            // Lead Capture
            'lead_error_spam'           => 'Request blocked for security (Spam detected).',
            'lead_error_whatsapp_req'   => 'WhatsApp field is required.',
            'lead_error_email_req'      => 'Invalid or required email.',
            'lead_error_whatsapp_invalid' => 'Invalid WhatsApp number.',
            'lead_error_lgpd_req'       => 'Accepting the privacy terms is required.',
            'lead_success_msg'          => 'Registration completed successfully!',
            'lead_error_duplicate'      => 'This email is already in our list or an error occurred.',
            'lead_error_server'         => 'Server error. Please reload the page and try again.',
            'lead_error_generic'        => 'An error occurred.',
            'lead_error_server_try'     => 'Server error, try again.',
            'lead_error_temp_mail'      => 'Temporary email service not allowed. Use your real email.',
            'lead_error_invalid_format' => 'Invalid email format. Try a real address.',
            'lead_error_no_mx'          => 'This email domain does not exist or does not accept emails.',
            'lead_placeholder_email'    => 'Enter your best email',
            'lead_btn_submit'           => 'Subscribe Now',
            'lead_redirecting'           => 'Redirecting, please wait...',
            'lead_sending'              => 'Sending...',
            'lead_form_title_default'   => 'Subscribe to our Newsletter',
            'lead_form_desc_default'    => 'Get the best tips and weekly updates for free.',
            'lead_form_lgpd_default'    => 'I agree to the Privacy Policy and accept receiving communications.',

            // Author Box & Bio
            'author_bio_fallback'       => 'Author at %s',
            'author_profile_btn'        => 'My Full Profile',
            'author_site_link'          => 'Author\'s Website',
            'lp_about_badge'            => 'Expert',
            'lp_service_read_more'      => 'Read More',
            'lp_hero_btn_default'       => 'Get Started',
            
            // Store Catalog & Related
            'widget_recent_products'    => 'Recent Products',
            'loja_catalog_badge_cat'    => 'Category',
            'loja_catalog_badge_store'  => 'Catalog',
            'loja_related_title'        => 'You may also like',
            'loja_filter_category'      => 'Filter by category',
            'loja_filter_all'           => 'All',
            'loja_nav_prev'             => 'Previous',
            'loja_nav_next'             => 'Next',
            'loja_nav_catalog'          => 'Catalog navigation',
            'loja_empty_category'       => 'No products found in this category.',
            
            // Contact Form
            'contact_label_name'         => 'Full Name',
            'contact_placeholder_name'   => 'Your name',
            'contact_label_email'        => 'Email',
            'contact_placeholder_email'  => 'your@email.com',
            'contact_label_subject'      => 'Subject',
            'contact_placeholder_subject' => 'What is it about?',
            'contact_label_message'      => 'Message',
            'contact_placeholder_message' => 'Write your message here...',
            'contact_btn_submit'         => 'Send Message',
            'contact_btn_sending'        => 'Sending...',
            'contact_msg_conn_error'     => 'Connection error. Try again later.',
            'contact_err_spam'           => 'Request blocked for security (Spam detected).',
            'contact_err_disabled'       => 'The contact form is currently disabled.',
            'contact_err_required'       => 'Please fill in all required fields correctly.',
            'contact_success_msg'        => 'Your message has been sent successfully! We will contact you soon.',
            'contact_err_mail'           => 'An error occurred while sending email. Please try again later.',
        ),

        // Espanhol (Fase 3)
        'es_ES' => array(
            // TOC (Sumário)
            'toc_title'                 => 'En este artículo:',
            'toc_toggle_hide'           => 'Ocultar sumario',
            'toc_toggle_show'           => 'Mostrar sumario',
            'toc_toggle_title'          => 'Mostrar/Ocultar',

            // Tempo de Leitura
            'reading_time_less_than_1'  => 'Menos de 1 min de lectura',
            'reading_time_suffix'       => 'min de lectura',

            // 404
            '404_title'                 => '¡Opa! Página no encontrada.',
            '404_text'                  => 'Parece que el enlace que seguiste ya no existe o se ha movido. ¿Qué tal intentar una búsqueda o consultar nuestros últimos artículos?',
            '404_btn_home'              => 'Volver al Inicio',
            '404_btn_search'            => 'Buscar en el Sitio',
            '404_recent_title'          => 'Artículos que te pueden interesar:',

            // Footer
            'footer_about'              => 'Acerca de',
            'footer_links'              => 'Enlaces',
            'footer_site'               => 'Sitio',
            'footer_home'               => 'Inicio',
            'footer_setup_menu'         => 'Configurar Menú de Pie de Página',
            'footer_follow'             => 'Síguenos',
            'footer_close'              => 'Cerrar',
            'footer_back_to_top'        => 'Volver arriba',
            'footer_text_default'       => '© [ano] [nome_site]. Todos los derechos reservados.',
            'footer_aux_text_default'   => 'Construido para lectores que buscan señal, no ruido.',
            'exit_intent_title_default' => '¡Espera! No te vayas todavía...',
            'exit_intent_desc_default'  => 'Tenemos un regalo especial para ti antes de salir.',

            // Header
            'header_open_menu'          => 'Abrir Menú',
            'header_close_menu'         => 'Cerrar Menú',
            'header_open_search'        => 'Abrir Búsqueda',
            'header_toggle_dark_mode'   => 'Alternar Modo Oscuro',
            'header_skip_link'          => 'Saltar al contenido principal',
            'header_close_search'       => 'Cerrar Búsqueda',
            'header_search_placeholder' => '¿Qué estás buscando?',
            'header_search_submit'      => 'Buscar',
            'header_search_help'        => 'Presiona Enter para buscar solo en nuestros artículos.',
            'header_cta_text_default'   => 'Contacto',

            // Index/Archive/Search
            'archive_search_label'      => 'Búsqueda',
            'archive_search_results'    => 'Resultados para: %s',
            'archive_prefix_category'   => 'Categoría',
            'archive_prefix_tag'        => 'Etiqueta',
            'archive_prefix_archive'    => 'Archivo',
            'archive_posts_label'       => 'Publicaciones',
            'archive_load_more'         => 'Ver Más Publicaciones',
            'archive_loading'           => 'Cargando...',
            'archive_error'             => 'Error. Intentar de nuevo',
            'archive_no_content'        => 'No se encontró ningún contenido.',

            // Single Post & Core
            'single_author_prefix'      => 'Por',
            'single_pages'              => 'Páginas:',
            'single_tags'               => 'Etiquetas: ',
            'single_related_title'      => 'También te puede gustar',
            'single_sidebar_recent'     => 'Publicaciones Recientes',
            'breadcrumbs_nav_label'     => 'Ruta de navegación',
            'categories_title'          => 'Categorías',
            'share_title'               => 'Compartir:',
            'share_whatsapp_label'      => 'Compartir en WhatsApp',
            'share_facebook_label'      => 'Compartir en Facebook',
            'share_twitter_label'      => 'Compartir en X (Twitter)',

            // LGPD Banner
            'lgpd_policy_btn'           => 'Política de Privacidad',
            'lgpd_accept_btn'           => 'Entendido y Acepto',
            'lgpd_text_default'         => 'Utilizamos cookies y datos de navegación para mejorar su experiencia. Al continuar, acepta nuestra Política de Privacidad.',

            // Lead Capture
            'lead_error_spam'           => 'Solicitud bloqueada por seguridad (Spam detectado).',
            'lead_error_whatsapp_req'   => 'El campo de WhatsApp es obligatorio.',
            'lead_error_email_req'      => 'Correo electrónico no válido o obligatorio.',
            'lead_error_whatsapp_invalid' => 'Número de WhatsApp no válido.',
            'lead_error_lgpd_req'       => 'Es obligatorio aceptar los términos de la LGPD.',
            'lead_success_msg'          => '¡Registro realizado con éxito!',
            'lead_error_duplicate'      => 'Este correo electrónico ya está en nuestra lista o ha ocurrido un error.',
            'lead_error_server'         => 'Error del servidor. Por favor, recargue la página e intente de nuevo.',
            'lead_error_generic'        => 'Ocurrió un error.',
            'lead_error_server_try'     => 'Error del servidor, intente de nuevo.',
            'lead_error_temp_mail'      => 'Servicio de correo electrónico temporal no permitido. Use su correo real.',
            'lead_error_invalid_format' => 'Correo electrónico con formato no válido. Intente con una dirección real.',
            'lead_error_no_mx'          => 'El dominio de este correo electrónico no existe o no acepta correos.',
            'lead_placeholder_email'    => 'Escriba su mejor correo',
            'lead_btn_submit'           => 'Quiero Recibir',
            'lead_redirecting'           => 'Redireccionando, espere por favor...',
            'lead_sending'              => 'Enviando...',
            'lead_form_title_default'   => 'Suscríbase a nuestro boletín',
            'lead_form_desc_default'    => 'Reciba los mejores consejos y actualizaciones semanales de forma gratuita.',
            'lead_form_lgpd_default'    => 'Acepto la Política de Privacidad y consiento recibir comunicaciones.',

            // Author Box & Bio
            'author_bio_fallback'       => 'Autor en %s',
            'author_profile_btn'        => 'Mi Perfil Completo',
            'author_site_link'          => 'Sitio del Autor',
            'lp_about_badge'            => 'Especialista',
            'lp_service_read_more'      => 'Saber Más',
            'lp_hero_btn_default'       => 'Comenzar Ahora',
            
            // Store Catalog & Related
            'widget_recent_products'    => 'Productos Recientes',
            'loja_catalog_badge_cat'    => 'Categoría',
            'loja_catalog_badge_store'  => 'Catálogo',
            'loja_related_title'        => 'También te puede gustar',
            'loja_filter_category'      => 'Filtrar por categoría',
            'loja_filter_all'           => 'Todos',
            'loja_nav_prev'             => 'Anterior',
            'loja_nav_next'             => 'Siguiente',
            'loja_nav_catalog'          => 'Navegación del catálogo',
            'loja_empty_category'       => 'No se encontraron productos en esta categoría.',
            
            // Contact Form
            'contact_label_name'         => 'Nombre Completo',
            'contact_placeholder_name'   => 'Su nombre',
            'contact_label_email'        => 'Correo electrónico',
            'contact_placeholder_email'  => 'tu@correo.com',
            'contact_label_subject'      => 'Asunto',
            'contact_placeholder_subject' => '¿De qué se trata?',
            'contact_label_message'      => 'Mensaje',
            'contact_placeholder_message' => 'Escriba su mensaje aquí...',
            'contact_btn_submit'         => 'Enviar Mensaje',
            'contact_btn_sending'        => 'Enviando...',
            'contact_msg_conn_error'     => 'Error de conexión. Intente más tarde.',
            'contact_err_spam'           => 'Solicitud bloqueada por seguridad (Spam detectado).',
            'contact_err_disabled'       => 'El formulario de contacto está desactivado por el momento.',
            'contact_err_required'       => 'Por favor, rellene todos los campos obligatorios correctamente.',
            'contact_success_msg'        => '¡Su mensaje ha sido enviado con éxito! Nos pondremos en contacto pronto.',
            'contact_err_mail'           => 'Ocurrió un error al enviar el correo electrónico. Intente de nuevo más tarde.',
        ),

        // Francês (Fase 4)
        'fr_FR' => array(
            // TOC (Sumário)
            'toc_title'                 => 'Dans cet article :',
            'toc_toggle_hide'           => 'Masquer le sommaire',
            'toc_toggle_show'           => 'Afficher le sommaire',
            'toc_toggle_title'          => 'Afficher/Masquer',

            // Tempo de Leitura
            'reading_time_less_than_1'  => 'Moins de 1 min de lecture',
            'reading_time_suffix'       => 'min de lecture',

            // 404
            '404_title'                 => 'Oups ! Page non trouvée.',
            '404_text'                  => 'Il semble que le lien que vous avez suivi n\'existe plus ou a été déplacé. Pourquoi ne pas essayer une recherche ou consulter nos derniers articles ?',
            '404_btn_home'              => 'Retour à l\'accueil',
            '404_btn_search'            => 'Rechercher sur le site',
            '404_recent_title'          => 'Articles qui pourraient vous intéresser :',

            // Footer
            'footer_about'              => 'À propos',
            'footer_links'              => 'Liens',
            'footer_site'               => 'Site',
            'footer_home'               => 'Accueil',
            'footer_setup_menu'         => 'Configurer le menu du pied de page',
            'footer_follow'             => 'Suivez-nous',
            'footer_close'              => 'Fermer',
            'footer_back_to_top'        => 'Retour en haut',
            'footer_text_default'       => '© [ano] [nome_site]. Tous droits réservés.',
            'footer_aux_text_default'   => 'Conçu pour les lecteurs qui recherchent le signal, pas le bruit.',
            'exit_intent_title_default' => 'Attendez ! Ne partez pas tout de suite...',
            'exit_intent_desc_default'  => 'Nous avons un cadeau spécial pour vous avant votre départ.',

            // Header
            'header_open_menu'          => 'Ouvrir le menu',
            'header_close_menu'         => 'Fermer le menu',
            'header_open_search'        => 'Ouvrir la recherche',
            'header_toggle_dark_mode'   => 'Basculer le mode sombre',
            'header_skip_link'          => 'Passer au contenu principal',
            'header_close_search'       => 'Fermer la recherche',
            'header_search_placeholder' => 'Que recherchez-vous ?',
            'header_search_submit'      => 'Rechercher',
            'header_search_help'        => 'Appuyez sur Entrée pour rechercher uniquement parmi nos articles.',
            'header_cta_text_default'   => 'Contact',

            // Index/Archive/Search
            'archive_search_label'      => 'Recherche',
            'archive_search_results'    => 'Résultats pour : %s',
            'archive_prefix_category'   => 'Catégorie',
            'archive_prefix_tag'        => 'Étiquette',
            'archive_prefix_archive'    => 'Archives',
            'archive_posts_label'       => 'Publications',
            'archive_load_more'         => 'Voir plus de publications',
            'archive_loading'           => 'Chargement...',
            'archive_error'             => 'Erreur. Réessayer',
            'archive_no_content'        => 'Aucun contenu trouvé.',

            // Single Post & Core
            'single_author_prefix'      => 'Par',
            'single_pages'              => 'Pages :',
            'single_tags'               => 'Étiquettes : ',
            'single_related_title'      => 'Vous aimerez aussi',
            'single_sidebar_recent'     => 'Publications récentes',
            'breadcrumbs_nav_label'     => 'Fil d\'Ariane',
            'categories_title'          => 'Catégories',
            'share_title'               => 'Partager :',
            'share_whatsapp_label'      => 'Partager sur WhatsApp',
            'share_facebook_label'      => 'Partager sur Facebook',
            'share_twitter_label'      => 'Partager sur X (Twitter)',

            // LGPD Banner
            'lgpd_policy_btn'           => 'Politique de confidentialité',
            'lgpd_accept_btn'           => 'J\'accepte',
            'lgpd_text_default'         => 'Nous utilisons des cookies et des données de navigation pour améliorer votre expérience. En continuant, vous acceptez notre Politique de confidentialité.',

            // Lead Capture
            'lead_error_spam'           => 'Demande bloquée par sécurité (Spam détecté).',
            'lead_error_whatsapp_req'   => 'Le champ WhatsApp est obligatoire.',
            'lead_error_email_req'      => 'E-mail non valide ou obligatoire.',
            'lead_error_whatsapp_invalid' => 'Numéro WhatsApp non valide.',
            'lead_error_lgpd_req'       => 'L\'acceptation des conditions de la RGPD est obligatoire.',
            'lead_success_msg'          => 'Inscription réussie !',
            'lead_error_duplicate'      => 'Cet e-mail est déjà dans notre liste ou une erreur est survenue.',
            'lead_error_server'         => 'Erreur du serveur. Veuillez recharger la page et réessayer.',
            'lead_error_generic'        => 'Une erreur est survenue.',
            'lead_error_server_try'     => 'Erreur du serveur, veuillez réessayer.',
            'lead_error_temp_mail'      => 'Service de messagerie temporaire non autorisé. Utilisez votre e-mail réel.',
            'lead_error_invalid_format' => 'Format d\'e-mail non valide. Essayez avec une adresse réelle.',
            'lead_error_no_mx'          => 'Le domaine de cet e-mail n\'existe pas ou n\'accepte pas les e-mails.',
            'lead_placeholder_email'    => 'Saisissez votre meilleur e-mail',
            'lead_btn_submit'           => 'Je veux recevoir',
            'lead_redirecting'           => 'Redirection, veuillez patienter...',
            'lead_sending'              => 'Envoi en cours...',
            'lead_form_title_default'   => 'Abonnez-vous à notre newsletter',
            'lead_form_desc_default'    => 'Recevez gratuitement nos meilleurs conseils et mises à jour hebdomadaires.',
            'lead_form_lgpd_default'    => 'J\'accepte la Politique de confidentialité et je consens à recevoir des communications.',

            // Author Box & Bio
            'author_bio_fallback'       => 'Auteur sur %s',
            'author_profile_btn'        => 'Mon profil complet',
            'author_site_link'          => 'Site Web de l\'auteur',
            'lp_about_badge'            => 'Spécialiste',
            'lp_service_read_more'      => 'En savoir plus',
            'lp_hero_btn_default'       => 'Commencer maintenant',
            
            // Store Catalog & Related
            'widget_recent_products'    => 'Produits Récents',
            'loja_catalog_badge_cat'    => 'Catégorie',
            'loja_catalog_badge_store'  => 'Catalogue',
            'loja_related_title'        => 'Vous aimerez aussi',
            'loja_filter_category'      => 'Filtrer par catégorie',
            'loja_filter_all'           => 'Tous',
            'loja_nav_prev'             => 'Précédent',
            'loja_nav_next'             => 'Suivant',
            'loja_nav_catalog'          => 'Navigation dans le catalogue',
            'loja_empty_category'       => 'Aucun produit trouvé dans cette catégorie.',
            
            // Contact Form
            'contact_label_name'         => 'Nom Complet',
            'contact_placeholder_name'   => 'Votre nom',
            'contact_label_email'        => 'E-mail',
            'contact_placeholder_email'  => 'votre@email.com',
            'contact_label_subject'      => 'Sujet',
            'contact_placeholder_subject' => 'De quoi s\'agit-il ?',
            'contact_label_message'      => 'Message',
            'contact_placeholder_message' => 'Écrivez votre message ici...',
            'contact_btn_submit'         => 'Envoyer le Message',
            'contact_btn_sending'        => 'Envoi en cours...',
            'contact_msg_conn_error'     => 'Erreur de connexion. Réessayez plus tard.',
            'contact_err_spam'           => 'Demande bloquée par sécurité (Spam détecté).',
            'contact_err_disabled'       => 'Le formulaire de contact est actuellement désactivé.',
            'contact_err_required'       => 'Veuillez remplir correctement tous les champs obligatoires.',
            'contact_success_msg'        => 'Votre message a été envoyé avec succès ! Nous vous contacterons bientôt.',
            'contact_err_mail'           => 'Une erreur est survenue lors de l\'envoi de l\'e-mail. Veuillez réessayer plus tard.',
        ),
    );
}

/**
 * Retorna a tradução da chave fornecida de acordo com o idioma ativo do tema.
 * 
 * @param string $key Chave de tradução.
 * @return string Texto traduzido ou a própria chave como fallback.
 */
function fwx_t( $key ) {
    $options = get_option( 'fast_webx_options' );
    $lang = isset( $options['theme_language'] ) ? $options['theme_language'] : 'pt_BR';

    $translations = fwx_get_theme_translations();

    // 1. Tenta recuperar a tradução no idioma selecionado
    if ( isset( $translations[$lang][$key] ) && $translations[$lang][$key] !== '' ) {
        return $translations[$lang][$key];
    }

    // 2. Se vazio ou não existir, faz o fallback para o Português do Brasil (pt_BR)
    if ( isset( $translations['pt_BR'][$key] ) && $translations['pt_BR'][$key] !== '' ) {
        return $translations['pt_BR'][$key];
    }

    // 3. Fallback final: retorna a própria chave formatada
    return $key;
}
