<?php

namespace Tests\Unit;

use App\Core\Request;
use PHPUnit\Framework\TestCase;

/**
 * Suite TEPU Objetivo 3 — Request::sanitizeValue vía get/post/all.
 *
 * Valida que las entradas HTTP se neutralizan (XSS/HTML/inyección básica)
 * antes de llegar a controladores y modelos.
 */
class RequestSanitizerTest extends TestCase
{
    private array $originalGet = [];
    private array $originalPost = [];

    protected function setUp(): void
    {
        $this->originalGet = $_GET;
        $this->originalPost = $_POST;
    }

    protected function tearDown(): void
    {
        $_GET = $this->originalGet;
        $_POST = $this->originalPost;
    }

    /**
     * Escenario GET: etiquetas HTML y atributos de evento eliminados por strip_tags.
     */
    public function testGetEliminaEtiquetasHtml(): void
    {
        $_GET['nombre'] = '<b>Juan</b><img src=x onerror=alert(1)>';

        $request = new Request();
        $valor = $request->get('nombre');

        $this->assertEquals('Juan', $valor);
        $this->assertStringNotContainsString('<', $valor);
        $this->assertStringNotContainsString('onerror', $valor);
    }

    /**
     * Escenario POST: payload XSS clásico (<script>) removido; conserva texto útil.
     */
    public function testPostNeutralizaScriptXss(): void
    {
        $_POST['mensaje'] = '<script>alert("xss")</script>Texto util';

        $request = new Request();
        $valor = $request->post('mensaje');

        $this->assertEquals('Texto util', $valor);
        $this->assertStringNotContainsString('<script>', $valor);
        $this->assertStringNotContainsString('alert', $valor);
    }

    /**
     * Escenario POST: cadena con comillas SQL + iframe → texto plano sin markup.
     */
    public function testPostFiltraCaracteresDeInyeccionSql(): void
    {
        $_POST['correo'] = "admin@test.com' OR '1'='1 -- <iframe></iframe>";

        $request = new Request();
        $valor = $request->post('correo');

        $this->assertEquals("admin@test.com' OR '1'='1 --", $valor);
        $this->assertStringNotContainsString('<iframe>', $valor);
    }

    /**
     * Escenario all(): sanitización uniforme de todo el cuerpo POST.
     */
    public function testAllSanitizaTodosLosCamposPost(): void
    {
        $_POST = [
            'nombre' => '<strong>Maria</strong>',
            'detalle' => '<svg/onload=alert(1)>Detalle',
            'id' => 42,
        ];

        $request = new Request();
        $datos = $request->all();

        $this->assertEquals('Maria', $datos['nombre']);
        $this->assertEquals('Detalle', $datos['detalle']);
        $this->assertEquals(42, $datos['id']);
    }

    /**
     * Escenario GET: entidades HTML decodificadas; bloques script eliminados por completo.
     */
    public function testGetDecodificaEntidadesHtmlPeligrosas(): void
    {
        $_GET['q'] = '&lt;script&gt;alert(1)&lt;/script&gt;Busqueda';

        $request = new Request();
        $valor = $request->get('q');

        $this->assertEquals('Busqueda', $valor);
        $this->assertStringNotContainsString('<script>', $valor);
        $this->assertStringNotContainsString('alert', $valor);
    }

    /**
     * Escenario POST: caracteres de control (null byte) eliminados del string.
     */
    public function testPostEliminaCaracteresDeControl(): void
    {
        $_POST['campo'] = "valor\x00oculto\r\n";

        $request = new Request();
        $valor = $request->post('campo');

        $this->assertEquals('valoroculto', $valor);
        $this->assertStringNotContainsString("\0", $valor);
    }

    /**
     * Escenario: clave inexistente retorna default sin excepción.
     */
    public function testGetRetornaDefaultCuandoClaveNoExiste(): void
    {
        $_GET = [];

        $request = new Request();

        $this->assertNull($request->get('inexistente'));
        $this->assertEquals('fallback', $request->get('inexistente', 'fallback'));
    }
}
