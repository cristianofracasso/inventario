<?php

use App\Http\Controllers\ColetaController;
use App\Http\Controllers\ProdutoController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

// Rota inicial (página de login)
Route::get('/', function () {
    return view('auth.login');
});

// Rotas de Autenticação
Route::group(['namespace' => 'Auth'], function () {
    Route::get('auth/login', [ProfileController::class, 'login'])->name('login');
    Route::post('/logout', [ProfileController::class, 'logout'])->name('logout');
});

// Rotas protegidas (requer autenticação)
Route::middleware(['auth'])->group(function () {
    // Rotas do ColetaController
    Route::get('/inicio', [ColetaController::class, 'index'])->name('index');
    Route::get('/iniciar-coleta', [ColetaController::class, 'iniciarColeta'])->name('iniciar.coleta');
    Route::post('/validar-palet', [ColetaController::class, 'validarPalet'])->name('validar.palet');

    // Rotas relacionadas a produtos
    Route::get('/produto', [ColetaController::class, 'exibirFormularioProduto'])->name('produto');
    Route::post('/validar-produto', [ColetaController::class, 'validarProduto'])->name('validar.produto');
    Route::post('/registrar-serial-produto', [ColetaController::class, 'registrarSerialProduto'])->name('registrar.serial.produto');
    Route::delete('/coleta/{id}', [ColetaController::class, 'excluirProduto'])->name('excluir.produto');
Route::post('/encerrar-produto', [ColetaController::class, 'encerrarProduto'])->name('encerrar.produto');
Route::get('/cadastrar-produto', function () {
    return redirect()->back();
})->name('cadastrar.produto');

Route::post('/salvar-produto', [ColetaController::class, 'salvar'])->name('salvar.produto');

    // Rotas relacionadas a serial
    Route::get('/serial', [ColetaController::class, 'exibirFormularioSerial'])->name('serial');
    Route::post('/registrar-serial', [ColetaController::class, 'registrarSerial'])->name('registrar.serial');
    Route::post('/excluir-ultimo-serial', [ColetaController::class, 'excluirUltimoSerial'])->name('excluir.ultimo.serial');

    // Outras rotas do ColetaController
    Route::get('/produtov', [ColetaController::class, 'exibirFormularioroduto'])->name('produtov');
    Route::get('/produtoserial', [ColetaController::class, 'exibirserial'])->name('produtoserial');
    Route::post('/finalizar-coleta', [ColetaController::class, 'finalizarColeta'])->name('finalizar.coleta');

    // Nova rota para a view de produtos com filtros
    Route::get('/produtos', [ProdutoController::class, 'index'])->name('produtos');
});
Route::get('/relatorio/exportar/{contagem}', [ProdutoController::class, 'exportarExcel'])
    ->name('relatorio.exportar');

    Route::get('/relatorio/lista', [ColetaController::class, 'lista'])->name('relatorio.lista');

// Rota para exportação
Route::get('/exportar-lista/{contagem}/{grupo}', [ColetaController::class, 'exportarLista'])
    ->name('relatorio.exportar.lista');

    // Adicione estas rotas no seu arquivo routes/web.php
// Adicione estas rotas no seu arquivo routes/web.php
Route::get('/divergencias', [App\Http\Controllers\ProdutoController::class, 'divergencias'])->name('divergencias');
Route::get('/divergencias/exportar/{contagem}', [App\Http\Controllers\ProdutoController::class, 'exportarExcelDivergencias'])->name('divergencias.exportar');

// Rotas de autenticação geradas pelo Laravel (como register, password reset, etc.)
require __DIR__.'/auth.php';