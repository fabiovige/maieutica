<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Response;

class DocumentationController extends Controller
{
    /**
     * Diretório base da documentação
     */
    private $basePath;

    public function __construct()
    {
        $this->basePath = base_path('documentation');
    }

    /**
     * Exibe a página principal da documentação (index.html)
     */
    public function index()
    {
        $filePath = $this->basePath . '/index.html';

        if (!File::exists($filePath)) {
            abort(404, 'Documentação não encontrada.');
        }

        $content = File::get($filePath);
        $content = $this->processHtmlPaths($content, 'index');
        return Response::make($content, 200, ['Content-Type' => 'text/html; charset=UTF-8']);
    }

    /**
     * Exibe páginas individuais da documentação
     *
     * @param string $filename Nome do arquivo HTML (sem extensão)
     */
    public function page($filename)
    {
        // Remove a extensão se o usuário passar
        $filename = str_replace('.html', '', $filename);

        $filePath = $this->basePath . '/pages/' . $filename . '.html';

        if (!File::exists($filePath)) {
            abort(404, 'Página de documentação não encontrada.');
        }

        $content = File::get($filePath);
        $content = $this->processHtmlPaths($content, 'page');
        return Response::make($content, 200, ['Content-Type' => 'text/html; charset=UTF-8']);
    }

    /**
     * Serve arquivos de assets (CSS, JS, imagens)
     *
     * @param string $type Tipo do asset (css, js, images)
     * @param string $filename Nome do arquivo
     */
    public function asset($type, $filename)
    {
        // Valida o tipo para evitar directory traversal
        $allowedTypes = ['css', 'js', 'images'];
        if (!in_array($type, $allowedTypes)) {
            abort(404, 'Tipo de asset não permitido.');
        }

        $filePath = $this->basePath . '/assets/' . $type . '/' . $filename;

        if (!File::exists($filePath)) {
            abort(404, 'Asset não encontrado.');
        }

        // Define o content-type baseado na extensão
        $mimeType = $this->getMimeType($filename);

        $content = File::get($filePath);
        return Response::make($content, 200, ['Content-Type' => $mimeType]);
    }

    /**
     * Processa o HTML para corrigir caminhos relativos
     *
     * @param string $content Conteúdo HTML
     * @param string $type Tipo da página (index ou page)
     * @return string
     */
    private function processHtmlPaths($content, $type)
    {
        $baseUrl = url('/documentation');

        if ($type === 'index') {
            // Corrige links de assets (CSS, JS)
            $content = str_replace('href="assets/', 'href="' . $baseUrl . '/assets/', $content);
            $content = str_replace('src="assets/', 'src="' . $baseUrl . '/assets/', $content);

            // Corrige links de páginas
            $content = str_replace('href="pages/', 'href="' . $baseUrl . '/pages/', $content);
            $content = str_replace('href="index.html"', 'href="' . $baseUrl . '"', $content);
        } else {
            // Para páginas internas, assets estão um nível acima
            $content = str_replace('href="../assets/', 'href="' . $baseUrl . '/assets/', $content);
            $content = str_replace('src="../assets/', 'src="' . $baseUrl . '/assets/', $content);

            // Corrige links de navegação entre páginas
            $content = preg_replace('/href="([0-9]{2}-[^"]+\.html)"/', 'href="' . $baseUrl . '/pages/$1"', $content);

            // Corrige link para index
            $content = str_replace('href="../index.html"', 'href="' . $baseUrl . '"', $content);
        }

        return $content;
    }

    /**
     * Retorna o MIME type baseado na extensão do arquivo
     *
     * @param string $filename
     * @return string
     */
    private function getMimeType($filename)
    {
        $extension = pathinfo($filename, PATHINFO_EXTENSION);

        $mimeTypes = [
            'css' => 'text/css',
            'js' => 'application/javascript',
            'png' => 'image/png',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'gif' => 'image/gif',
            'svg' => 'image/svg+xml',
            'woff' => 'font/woff',
            'woff2' => 'font/woff2',
            'ttf' => 'font/ttf',
            'eot' => 'application/vnd.ms-fontobject',
        ];

        return $mimeTypes[$extension] ?? 'application/octet-stream';
    }
}
