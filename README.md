# leepdfs

La idea de este proyecto es crear scripts que extraigan información de las escrituras de titulización de hipotecas para
almacenar los datos en una base de datos y permitir a los hipotecados saber si su hipoteca ha sido titulizada y ya no
pertenece al banco con el que la contrataron.

Esto puede frenar un deshaucio.

Actualmente solo existe el dorectorio <ejemplo>, que contiene un script y un documento pdf con la lista de activos que forman parte
de un fondo titulizado. El script convierte la información del documento a un csv con tabuladores que permite su procesado.
Este script hace uso de la utilidad pdftohtml.

La idea es hacer el desarrollo en php para que permita usarlo en una aplicación web


Por supuesto, todo el codigo que se desarrolle se encontrará bajo licencia GPLv2.
