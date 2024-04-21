# MVP: TellMe (plugin WordPress + IOS app)

Este repositorio contiene dos carpetas principales que corresponden a dos proyectos diferentes: un plugin de WordPress y una aplicación para iOS. A continuación se describe el contenido y la funcionalidad de cada uno de estos proyectos.

## Estructura del Repositorio

El repositorio está organizado en las siguientes carpetas:

- `wordpress-plugin`: Contiene todos los archivos necesarios para el plugin de WordPress.
- `ios-app`: Contiene el código fuente de la aplicación iOS desarrollada en Swift.

### wordpress-plugin

El plugin de WordPress en esta carpeta tiene como objetivo principal crear un endpoint que permita guardar audios. Además, tiene la capacidad de generar posts automáticos a partir de estos audios, transcribiéndolos y creando versiones de texto basadas en el contenido auditivo.

#### Características principales:

- **Creación de Endpoint**: Permite guardar audios enviados desde la aplicación iOS.
- **Transcripción Automática**: Convierte los audios en texto para generar borradores de posts.
- **Creación de Posts**: Publica automáticamente los posts transcritos en tu sitio de WordPress.

### ios-app

La aplicación iOS está desarrollada en Swift y permite a los usuarios grabar audios que pueden ser enviados al plugin de WordPress mencionado anteriormente.

#### Notas importantes:

- **Cambio de Dominio**: Antes de utilizar la aplicación, asegúrate de reemplazar `my-domain.com` en el código fuente por el dominio de tu servidor de WordPress donde está instalado el plugin.

#### Características principales:

- **Grabación de Audio**: Los usuarios pueden grabar audios directamente desde su dispositivo iOS.
- **Envío de Audio**: Los audios grabados se pueden enviar al servidor de WordPress para su procesamiento y transcripción.

## Contribuir

Si deseas contribuir a alguno de los proyectos, simplemente haz difusión de ellos y dale a la estrellita.
