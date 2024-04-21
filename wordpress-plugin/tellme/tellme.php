<?php
/**
 * Plugin Name: Tellme
 * Plugin URI: https://eggemplo.com
 * Description: Tellme
 * Version: 1.0
 * Author: eggemplo
 */

 require_once 'core/TellmeIA.php';
 require_once 'core/TellmeAdmin.php';
require_once 'core/TellmeShortcodes.php';

// creamos un endpoint donde recibiremos un fichro de audio
add_action('rest_api_init', 'tellme_register_routes');
function tellme_register_routes() {
    register_rest_route('tellme/v1', '/audio', array(
        'methods' => 'POST',
        'callback' => 'tellme_audio'
    ));
}

function tellme_audio($request) {
    error_log('tellme_audio');

    $file = $_FILES['file'];
    $file_name = $file['name'];
    $file_tmp = $file['tmp_name'];
    $file_size = $file['size'];
    $file_error = $file['error'];
    $file_type = $file['type'];

    $file_ext = explode('.', $file_name);
    $file_ext = strtolower(end($file_ext));

    error_log($file_ext);

    $allowed = array('mp3', 'wav', 'ogg', 'flac', 'm4a');

    if (in_array($file_ext, $allowed)) {
        error_log('tellme_audio 2');
        if ($file_error === 0) {
            error_log('tellme_audio 3');
            if ($file_size < 10000000) {
                error_log('tellme_audio 4');
                // usamos la fecha y hora actual para el nombre del fichero
                $file_name_new = date('Ymd_His') . '.' . $file_ext;


                //$file_name_new = uniqid('', true) . '.' . $file_ext;
                // si no existe la carpeta la creamos
                $upload_dir = wp_upload_dir();
                $upload_dir = $upload_dir['basedir'];
                $upload_dir = $upload_dir . '/tellme_audios';
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0777);
                }
                $file_destination = $upload_dir . '/' . $file_name_new;

                move_uploaded_file($file_tmp, $file_destination);
                return rest_ensure_response(array('status' => 'success', 'message' => 'File uploaded successfully'));
            } else {
                return rest_ensure_response(array('status' => 'error', 'message' => 'File size too big'));
            }
        } else {
            return rest_ensure_response(array('status' => 'error', 'message' => 'Error uploading file'));
        }
    } else {
        return rest_ensure_response(array('status' => 'error', 'message' => 'Invalid file type'));
    }
}



// menu en el admin
add_action('admin_menu', 'tellme_menu');
function tellme_menu() {
    add_menu_page('Tellme', 'Audios', 'manage_options', 'tellme', 'tellme_page', 'dashicons-microphone', 99);

    add_submenu_page(
        'tellme',
        'Create audio',
        'Create audio',
        'manage_options',
        'tellme-create-audio',
        array('TellmeAdmin', 'submenu_page_callback')
    );

    // subpage for tocken settings
    add_submenu_page(
        'tellme',
        'Settings',
        'Settings',
        'manage_options',
        'tellme-settings',
        'tellme_settings_page'
    );

}

function tellme_page() {
    echo '<h1>Tellme</h1>';

    if (isset($_GET['audio'])) {
        $audio = $_GET['audio'];
        // audio url
        $upload_dir = wp_upload_dir();
        $upload_dir = $upload_dir['basedir'];
        $upload_dir = $upload_dir . '/tellme_audios';
        $audio = $upload_dir . '/' . $audio;

        if ( isset($_GET['action']) ) {
            switch ($_GET['action']) {
                case 'generatepost':
                    // llamamos a la IA para que pase el audio a texto
                    $tellme = new TellmeIA();
                    $text = $tellme->audioToText($audio);

                    // notice
                    echo '<div class="notice notice-success">';
                    echo '<h2>Text transcription</h2>';
                    echo '<p>' . $text . '</p>';
                    echo '<hr>';

                    // generamos un post con el texto y como titulo el nombre del archivo sin extensión. En borrador
                    $post = array(
                        'post_title' => pathinfo($audio, PATHINFO_FILENAME),
                        'post_content' => $text,
                        'post_status' => 'draft'
                    );
                    $post_id = wp_insert_post($post);

                    // mostramos el mensaje de que el post está creado y un enlace a la edición del post
                    echo '<p>Post created <a href="edit.php">Edit post</a></p>';
                    echo '</div>';
                    break;
                case 'generateAIpost':
                    // llamamos a la IA para que pase el audio a texto
                    $tellme = new TellmeIA();
                    $text = $tellme->audioToText($audio);

                    // notice
                    echo '<div class="notice notice-success">';
                    echo '<h2>Text transcription</h2>';
                    echo '<p>' . $text . '</p>';
                    echo '<hr>';

                    $prompt = get_option('tellme_prompt');

                    $new_content = $tellme->chatGPT($prompt . ' [' . $text . ']');
                    $new_title = $tellme->chatGPT('Dame un título para el post cuyo contenido es: [' . $new_content . ']');
                    // generamos un post con el texto y como titulo el nombre del archivo sin extensión. En borrador
                    $post = array(
                        'post_title' => pathinfo($audio, PATHINFO_FILENAME) . ' - ' . $new_title,
                        'post_content' => $new_content,
                        'post_status' => 'draft'
                    );
                    $post_id = wp_insert_post($post);

                     // imagen destacada
                     $image_url = $tellme->generateImage('Teniendo en cuenta la siguiente temática: "' . $text . '", genérame una imagen destacada oara la página web.');
                     // subimos la imagen como imagen destacada de la página
                    $new_image_id = media_sideload_image($image_url, $post_id, 'image', 'id');

                    // update featured image
                    set_post_thumbnail($post_id, $new_image_id);
                    
                    // mostramos el mensaje de que el post está creado y un enlace a la edición del post
                    echo '<p>Post created <a href="edit.php">Edit post</a></p>';
                    echo '</div>';
                    break;
                case 'delete':
                    // borramos el audio
                    unlink($audio);
                    // mostramos un mensaje de que el audio ha sido borrado
                    echo '<div class="notice notice-success">';
                    echo '<p>Audio deleted</p>';
                    echo '</div>';
                    break;
            }
        }
    }

   // mostramos un listado de los audios
    $upload_dir = wp_upload_dir();
    $upload_url = $upload_dir['baseurl'];

    $upload_dir = $upload_dir['basedir'];
    $upload_dir = $upload_dir . '/tellme_audios';
    $audios = scandir($upload_dir);

    
    // tabla de audios con 2 botones para generar el post, siguiendo el estadar html del admin de wordpress
    echo '<table class="wp-list-table widefat fixed striped">';
    echo '<thead>';
    echo '<tr>';
    echo '<th>Audio</th>';
    echo '<th>Actions</th>';
    echo '</tr>';
    echo '</thead>';
    echo '<tbody>';
    foreach ($audios as $audio) {
        if ($audio != '.' && $audio != '..') {
            echo '<tr>';
            echo '<td>';
            // botón play del audio con su url
            echo '<audio controls>';
            echo '<source src="' . $upload_url . "/tellme_audios/" . $audio . '" type="audio/wav">';
            echo '</audio>';
            echo $audio;
            echo '</td>';
            echo '<td>';
            echo '<a class="button" style="margin-right:5px;" href="admin.php?page=tellme&action=generatepost&audio=' . $audio . '">Generate post</a>';
            echo '<a class="button" style="margin-right:5px;" href="admin.php?page=tellme&action=generateAIpost&audio=' . $audio . '">Generate creative post 💫</a>';
            // botón delete con dashicon
            echo '<a class="button" style="color:#d63638; border-color:#d63638;" href="admin.php?page=tellme&action=delete&audio=' . $audio . '"><span class="dashicons dashicons-trash"></span></a>';
            
            echo '</td>';
            echo '</tr>';
        }
    }
    echo '</tbody>';
    echo '</table>';



    /*
    echo '<ul>';
    foreach ($audios as $audio) {
        if ($audio != '.' && $audio != '..') {
            echo '<li>';
            echo $audio;
            echo '<a class="button" href="admin.php?page=tellme&audio=' . $audio . '">Generate post</a>';
            echo '</li>';
        }
    }
    */
}

// Render the settings page
function tellme_settings_page() {
    // Handle form submission
    if (isset($_POST['submit'])) {
        // Get API key
        $apikey = $_POST['apikey'];
        // Update the API key
        update_option('tellme_apikey', $apikey);

        // Get token
        $token = $_POST['token'];
        // Update the token
        update_option('tellme_token', $token);

        // Get prompt
        $prompt = $_POST['prompt'];
        // Update the prompt
        update_option('tellme_prompt', $prompt);

        // Display success message
        echo '<div class="notice notice-success"><p>API key updated successfully!</p></div>';
    }

    // Render the settings page form
    echo '<div class="wrap">';
    echo '<h1>Tellme Settings</h1>';
    echo '<form method="post">';
    echo '<label for="apikey">OpenAI API key:</label>';
    echo '<br>';
    echo '<input type="text" name="apikey" class="large-text" value="' . get_option('tellme_apikey') . '">';
    echo '<br>';
    // prompt
    echo '<label for="prompt">Prompt:</label>';
    echo '<br>';
    echo '<input type="text" name="prompt" class="large-text" value="' . get_option('tellme_prompt') . '">';
    echo '<br>';
    echo '<input type="submit" name="submit" value="Update API Key" class="button">';
    // token
    echo '<br>';

    /*
    echo '<label for="token">Token:</label>';
    echo '<br>';
    echo '<input type="text" name="token" class="large-text" value="' . get_option('tellme_token') . '">';
    */
    echo '</form>';

    echo '</div>';
}