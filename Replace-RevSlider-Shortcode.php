<?php
/*
Plugin Name: ReplaceRSS
Description: Replace-RevSlider-Shortcode
Plugin para reemplazar un shortcode de Revolution Slider por otro en una página específica.
Version: 1.0
Author: guishex2001
*/

// Función para activar el plugin
function activar_plugin() {
    // Aquí puedes realizar cualquier configuración necesaria al activar el plugin
}

// Función para desactivar el plugin
function desactivar_plugin() {
    // Aquí puedes realizar cualquier limpieza necesaria al desactivar el plugin
}

// Función para borrar el plugin
function borrar_plugin() {
    // Aquí puedes realizar cualquier limpieza necesaria al borrar el plugin
}

// Registrando los hooks de activación, desactivación y desinstalación del plugin
register_activation_hook(__FILE__, 'activar_plugin');
register_deactivation_hook(__FILE__, 'desactivar_plugin');
register_uninstall_hook(__FILE__, 'borrar_plugin');

// Agregar el menú de administración
add_action('admin_menu', 'crear_menu');

function crear_menu() {
    add_menu_page(
        'Replace RevSlider Shortcode',
        'Replace RevSlider Shortcode',
        'manage_options',
        'rrs_menu',
        'mostrar_contenido',
        plugin_dir_url(__FILE__) . 'admin/img/icon.png',
        2
    );
}

// Función para mostrar el contenido de la página del menú
function mostrar_contenido() {
    global $wpdb;

    // Obtener todas las páginas de WordPress
    $pages = get_pages();

    ?>
    <div class="replace-revslider-shortcode">
        <h1>Bienvenido al Plugin Replace RevSlider Shortcode</h1>
        <p>Selecciona una página y un slider para reemplazar el shortcode de Revolution Slider.</p>
        <form method="post">
            <label for="page">Selecciona la página:</label>
            <select name="page" id="page" style="width: 300px;">
                <option value="">Selecciona una página</option>
                <?php foreach ($pages as $page) : ?>
                    <option value="<?php echo $page->ID; ?>"><?php echo $page->post_title; ?></option>
                <?php endforeach; ?>
            </select>
            <br>
            <label for="slider">Selecciona el slider:</label>
            <select name="slider" id="slider" style="width: 300px;">
                <!-- Aquí se cargarán dinámicamente los sliders de Revolution Slider -->
            </select>
            <br>
            <input type="submit" name="submit" value="Aplicar cambios">
        </form>
    </div>
    <style>
        .replace-revslider-shortcode {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f9f9f9;
            border: 1px solid #ddd;
            border-radius: 5px;
        }

        .replace-revslider-shortcode h1 {
            font-size: 24px;
            margin-bottom: 10px;
            text-align: center;
        }

        .replace-revslider-shortcode p {
            font-size: 16px;
            margin-bottom: 20px;
            text-align: center;
        }

        .replace-revslider-shortcode form {
            text-align: center;
        }

        .replace-revslider-shortcode label {
            font-size: 16px;
            margin-right: 10px;
        }

        .replace-revslider-shortcode select {
            font-size: 16px;
            padding: 5px;
            margin-bottom: 10px;
            width: 100%;
            box-sizing: border-box;
        }

        .replace-revslider-shortcode input[type="submit"] {
            font-size: 16px;
            padding: 10px 20px;
            background-color: #0073aa;
            color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        .replace-revslider-shortcode input[type="submit"]:hover {
            background-color: #005580;
        }
    </style>
    <script>
        // Función para cargar dinámicamente los sliders cuando se seleccione una página
        jQuery(document).ready(function ($) {
            $('#page').change(function () {
                var page_id = $(this).val();
                $.ajax({
                    url: ajaxurl,
                    type: 'post',
                    data: {
                        action: 'cargar_sliders',
                        page_id: page_id
                    },
                    success: function (response) {
                        $('#slider').html(response);
                    }
                });
            });
        });
    </script>
    <?php
}

// Función para cargar dinámicamente los sliders de RevSlider para una página específica
add_action('wp_ajax_cargar_sliders', 'cargar_sliders_callback');
function cargar_sliders_callback() {
    global $wpdb;
    $page_id = $_POST['page_id'];

    // Obtener todos los sliders de Revolution Slider
    $sliders = $wpdb->get_results("SELECT id, title, alias FROM {$wpdb->prefix}revslider_sliders");

    // Construir el HTML de las opciones de los sliders
    $options_html = '';
    foreach ($sliders as $slider) {
        $options_html .= "<option value='{$slider->alias}'>{$slider->title}</option>";
    }

    echo $options_html;
    exit;
}

// Función para reemplazar el shortcode de RevSlider en la página seleccionada
add_action('init', 'reemplazar_shortcode');
function reemplazar_shortcode() {
    if (isset($_POST['submit']) && isset($_POST['page']) && isset($_POST['slider'])) {
        $page_id = $_POST['page'];
        $slider_alias = $_POST['slider'];

        // Obtener el contenido de la página
        $page_content = get_post_field('post_content', $page_id);

        // Construir el nuevo shortcode con el alias del slider seleccionado
        $new_shortcode = "[rev_slider alias=\"$slider_alias\"][/rev_slider]";

        // Reemplazar el shortcode de RevSlider en la página por el nuevo shortcode
        $updated_content = preg_replace('/\[rev_slider[^\]]+alias="[^"]+"\][^\]]*\[\/rev_slider\]/', $new_shortcode, $page_content);

        // Actualizar el contenido de la página
        wp_update_post(array(
            'ID' => $page_id,
            'post_content' => $updated_content
        ));
    }
}

