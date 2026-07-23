<?php

namespace App\Gigamed\Modele\DataObject;

enum TypeDocument: string
{
    case PDF  = 'pdf';
    case JPEG = 'jpeg';
    case JPG  = 'jpg';
    case PNG  = 'png';
    case DOCX = 'docx';
    case DOC  = 'doc';
    case XLSX = 'xlsx';
    case XLS  = 'xls';
    case PPTX = 'pptx';
    case PPT  = 'ppt';
}