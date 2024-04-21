<?php

class TellmeAdmin {
    public static function init() {

    }

    public static function submenu_page_callback() {

        if ( isset( $_POST['submit'] ) ) {
            $upload_dir = wp_upload_dir();
            $upload_dir = $upload_dir['basedir'];
            $upload_dir = $upload_dir . '/tellme_audios';
            if ( ! is_dir( $upload_dir ) ) {
                mkdir( $upload_dir, 0777 );
            }
            $file_name_new = 'audio_' . time() . '.wav';
            $file_destination = $upload_dir . '/' . $file_name_new;
            $audio = $_POST['audio'];
            $audio = explode( ',', $audio );
            $audio = base64_decode( $audio[1] );
            file_put_contents( $file_destination, $audio );

            // notice audio saves with a link to Audios section
            echo '<div class="notice notice-success is-dismissible">
                <p>Audio saved <a href="' . admin_url( 'admin.php?page=tellme' ) . '">Audios</a></p>
            </div>';
        }

        // formnulario desde donde con un botón de grabar y parar el usuario puede grabar un audio
        echo '<div class="wrap">';
        echo '<h2>Crear audio</h2>';
        echo '<button  id="record" class="button button-primary" value="Grabar">';
        echo 'Grabar';
        echo '</button>';

        // el formulario envía el audio grabado

        echo '<form id="tellme-form" action="" method="post" enctype="multipart/form-data">';
        settings_fields('tellme');
        do_settings_sections('tellme');
        
        echo '<input type="file" name="audioFile"  disabled style="display:none;" />';

        // submt
        echo '<input type="submit" name="submit" id="submit" class="button button-primary" value="Save">';
        echo '</form>';
        echo '</div>';

        // script que se encarga de grabar el audio

        echo '<script>
            var form = document.getElementById("tellme-form");
            var submit = document.getElementById("record");
            var audio = document.createElement("audio");
            var audioStream = null;
            var audioChunks = [];
            var mediaRecorder = null;

            submit.addEventListener("click", function(e) {
                if (mediaRecorder === null) {
                    navigator.mediaDevices.getUserMedia({ audio: true })
                        .then(function(stream) {
                            audioStream = stream;
                            mediaRecorder = new MediaRecorder(stream);
                            mediaRecorder.ondataavailable = function(e) {
                                audioChunks.push(e.data);
                            };
                            mediaRecorder.onstop = function() {
                                // lo guardams como input para el formulario
                                var blob = new Blob(audioChunks, { type: "audio/wav" });
                                var url = URL.createObjectURL(blob);
                                audio.src = url;
                                audio.controls = true;
                                form.appendChild(audio);
                                var reader = new FileReader();
                                reader.onload = function(e) {
                                    var audioInput = document.createElement("input");
                                    audioInput.type = "hidden";
                                    audioInput.name = "audio";
                                    audioInput.value = reader.result;
                                    form.appendChild(audioInput);
                                };

                                // leemos el blob y lo guardamos como input file del formulario
                                reader.readAsDataURL(blob);

                                // set file
                                var file = new File([blob], "audio.wav", { type: "audio/wav" });
                                var fileInput = document.querySelector("input[type=file]");
                                fileInput.files = new FileList([file]);



                                //reader.readAsDataURL(blob);
                            };
                            mediaRecorder.start();
                        })
                        .catch(function(err) {
                            console.log(err);
                        });
                } else {
                    mediaRecorder.stop();
                    audioStream.getTracks().forEach(function(track) {
                        track.stop();
                    });
                    audioStream = null;
                    mediaRecorder = null;
                }
            });

            
        </script>';

    
    }
}
TellmeAdmin::init();