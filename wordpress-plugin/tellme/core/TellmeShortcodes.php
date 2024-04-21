<?php

class TellmeShortcodes {
    public static function init() {
        add_shortcode('tellme', array('TellmeShortcodes', 'tellme_shortcode'));
    }

    public static function tellme_shortcode($attr, $content = null) {

        // si el usuario ha enviado el formulario
        if (isset($_POST['submit'])) {
            // guardamos el audio en el servidor
            $upload_dir = wp_upload_dir();
            $upload_dir = $upload_dir['basedir'];
            $upload_dir = $upload_dir . '/tellme_audios';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777);
            }
            $file_name_new = 'audio_' . time() . '.wav';
            $file_destination = $upload_dir . '/' . $file_name_new;
            $audio = $_POST['audio'];
            $audio = explode(',', $audio);
            $audio = base64_decode($audio[1]);
            file_put_contents($file_destination, $audio);

            // notice audio saves with a link to Audios section
            echo '<div class="notice notice-success is-dismissible">
                <p>Audio saved</p>
            </div>';
        }

        ob_start();
        ?>
        <div class="wrap">
            <h2>Crear audio</h2>
            <button id="record" class="button button-primary" value="Grabar">Grabar</button>
            <hr style="margin: 20px 0px; width: 40%;">
            <form id="tellme-form" action="" method="post" enctype="multipart/form-data">
                <input type="submit" name="submit" id="submit" class="button button-primary" value="Guardar audio">
            </form>
        </div>
        <script>
            var form = document.getElementById("tellme-form");
            var submit = document.getElementById("record");
            var audio = document.createElement("audio");
            var audioStream = null;
            var audioChunks = [];
            var mediaRecorder = null;

            submit.addEventListener("click", function(e) {
                if (navigator.mediaDevices) {
                    if (mediaRecorder === null) {
                        navigator.mediaDevices.getUserMedia({ audio: true })
                            .then(function(stream) {
                                audioStream = stream;
                                mediaRecorder = new MediaRecorder(stream);
                                mediaRecorder.ondataavailable = function(e) {
                                    audioChunks.push(e.data);
                                };
                                mediaRecorder.onstop = function(e) {
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
                                    // cambiamos el titulo del botón
                                    submit.textContent = "Seguir grabando";

                                    // leemos el blob y lo guardamos como input file del formulario
                                    reader.readAsDataURL(blob);
                                };
                                mediaRecorder.start();
                                submit.textContent = "Pausar";
                            });
                    } else {
                        mediaRecorder.stop();
                        audioStream.getTracks().forEach(function(track) {
                            track.stop();
                        });
                        mediaRecorder = null;
                    }
                } else {
                    alert("No se puede grabar audio");
                }
            });
        </script>
        <?php
        return ob_get_clean();
    }
}
TellmeShortcodes::init();