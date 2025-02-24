<?php

namespace App\Http\Controllers;

use App\Exports\RelatorioListaExport;
use App\Models\Area;
use App\Models\AreaCount;
use App\Models\Coleta;
use App\Models\Contagem;
use App\Models\Custos;
use App\Models\Group;
use App\Models\Grupo;
use App\Models\InventoryCount;
use App\Models\Produto;
use GuzzleHttp\Promise\Create;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class ColetaController extends Controller
{
    
    public $recount;
public $novaContagem; // Variável para armazenar o valor incrementado

public function __construct() 
{
    // Recupera o valor atual da contagem
    $this->recount = Grupo::where('nome', Auth::user()->grupo)
        ->get()
        ->fresh();

        $this->novaContagem = $this->recount->first()->contagem +1;

       
}

    public function index()
{
    if($this->recount->first()->status === "em_andamento")  {
        $areasNaoCont = Area::whereNotExists(function($query) {
            $query->select(DB::raw(1))
                  ->from('coletas')
                  ->whereColumn('coletas.codigo_palet', 'areas.id')
                  ->where(function($q) {
                      $q->where(function($sub) {
                          $sub->where('coletas.contagem', $this->recount)
                              ->orWhere('coletas.status', 'em_andamento');
                      })
                      ->where('coletas.grupo', Auth::user()->grupo);
                  });
        })
        ->select('areas.*')
        ->distinct()
        ->get();
                                
                            
                    //dd($this->conta);
                    if($areasNaoCont){
                    //   return redirect()->route('iniciar.coleta', ['areasNaoCont' => $areasNaoCont, 'areasCont' => $areasCont, 'areasOpen' => $areasOpen]);
                    return redirect()->route('iniciar.coleta');
                    }

                }
   

           return view('index');
    }

    public function iniciarColeta()
    { 
        $this->recount = Grupo::where('nome', Auth::user()->grupo)
        ->get()
        ->fresh();

        $this->novaContagem = $this->recount->first()->contagem +1;
        
        if($this->recount->first()->status === "finalizada") {
                    Grupo::where('nome', Auth::user()->grupo)
                            ->update([
                                'contagem'=> $this->novaContagem,
                                'status'=> 'em_andamento']);
                            }
                          

        $status = [
            'total_areas' => Area::count(),
            'areas_contadas' => Area::whereHas('coletas', function($query) {
                $query->where('contagem', $this->recount->first()->contagem)
                      ->where('status', 'finalizada')
                      ->where('grupo', Auth::user()->grupo);
            })->get(),
            'areas_em_andamento' => Area::whereHas('coletas', function($query) {
                $query->where('contagem', $this->recount->first()->contagem)
                      ->where('status', 'em_andamento')
                      ->where('grupo', Auth::user()->grupo);
            })->get(),
            'areas_pendentes' => Area::whereDoesntHave('coletas', function($query) {
                $query->where(function($q) {
                    $q->where('contagem', $this->recount->first()->contagem)
                      ->orWhere('status', 'em_andamento');
                })
                ->where('grupo', Auth::user()->grupo);
            })->get()
        ];
//dd( $status);
    
        return view('leitura-palet', compact('status'));

    }
   
    public function validarPalet(Request $request)
    {

        $request->validate([
            'codigo_palet' => 'required'
        ], [
            'codigo_palet.required' => 'O código da Área é obrigatório.'
        ]);

        // Verifica se já existe uma coleta finalizada com este palet
        $coletaExistente = Coleta::where('codigo_palet', $request->codigo_palet)
                                ->where('status', 'finalizada')
                                ->where('grupo', Auth::user()->grupo)
                                ->where('contagem', $this->recount->first()->contagem)
                                ->first();

        if ($coletaExistente) {
            return back()->withErrors(['codigo_palet' => 'Esta Área já foi coletada anteriormente.']);
        }

          // Verifica se a area existe
          $areaExistente = Area::where('id', $request->codigo_palet)
                  ->first();

        if (!$areaExistente) {
        return back()->withErrors(['codigo_palet' => 'Esta Área não existe.']);
        }

        

        


        // Caso contrário, salva o código do palet na sessão
        session(['codigo_palet' => $request->codigo_palet]);

        $itens = Coleta::where('codigo_palet', session('codigo_palet') )
                        ->where('grupo', Auth::user()->grupo)
                        ->where('contagem', $this->recount->first()->contagem)
                        ->get();

        $totalItens = $itens->count();
            
        return view('produto', compact('itens', 'totalItens'));
    }

    public function exibirFormularioProduto()
    {
       $itens = Coleta::where('codigo_palet', session('codigo_palet') )
                        ->where('grupo', Auth::user()->grupo)
                        ->where('contagem', $this->recount->first()->contagem)
                        ->orderBy('created_at', 'desc')
                        ->get();

                        $totalItens = $itens->count();
            
                        return view('produto', compact('itens', 'totalItens'));
    }

    
    // Validar código do produto
    public function validarProduto(Request $request)
    {
        // Validação do código do produto
        $request->validate([
            'codigo_produto' => 'required|string',
        ], [
            'codigo_produto.required' => 'O código do produto é obrigatório.',
        ]);

        $codAtivo = Produto::where('codigo_barras', $request->codigo_produto)
                            ->where('ativo', 1)
                            ->first();

        // Armazena o código do produto na sessão
        session(['codigo_produto' => $codAtivo->codigo_barras]);


        // Redireciona para a tela de serial
        return redirect()->route('produtov');
    }

    // Exibir formulário de serial
    public function exibirFormularioSerial()
    {
        // Verifica se o código do produto está na sessão
        if (!session('codigo_produto')) {
            return redirect()->route('produto')->with('error', 'Código do produto não encontrado.');
        }
           
        return view('serial');
    }

    // Exibir formulário de serial
    public function exibirFormularioroduto()
    {
       // Verifica se o produto existe
       $produtoExistente = Produto::where('codigo_barras', session('codigo_produto'))
                                    ->where('ativo', 1)
                                    ->first();


        if (!$produtoExistente) {
            return redirect()->route('produto')->withErrors(['codigo_produto' => 'Código do produto não encontrado.']);
        }

        $cod_prod = $produtoExistente->id;


        $prod_cust = Custos::where('produto_id',$cod_prod = $produtoExistente->id)
        ->where('empresa_id', 1)
        ->where('destino_estoque_id', 9)
        ->value('valor_custo_medio');

        if($prod_cust === NULL){
            $prod_cust = 0.00;
        }
            
session(['produto_custo' => $prod_cust]);

        return redirect()->route('produtoserial');

    }
        
    public function exibirserial() {
        // Verifica se o produto tem serial
        $controlaserie = Produto::where('codigo_barras', session('codigo_produto'))
                                        ->where('ativo', 1)
                                        ->first();

            if ($controlaserie->controla_numero_serie !== '1') {
            Coleta::create([
                'codigo_palet' => session('codigo_palet'),
                'sku' => session('codigo_produto'),
                'status' => 'em_andamento',
                'custo' => session('produto_custo'),
                'contagem' => $this->recount->first()->contagem,
                'grupo' => Auth::user()->grupo,
            ]);
    
            session()->forget('codigo_produto','produto_custo');
            return redirect()->route('produto')->with('success', 'Produto registrado com sucesso!');
        }

            
        // Se tem serial, busca os seriais já coletados
        $seriais = Coleta::where('codigo_palet', session('codigo_palet'))
            ->where('sku', session('codigo_produto'))
            ->where('contagem', $this->recount->first()->contagem)
            ->where('grupo', Auth::user()->grupo)
            ->orderBy('created_at', 'desc')
            ->get();

        $totalSerie = $seriais->count();
    
        return view('serial-produto', compact('seriais', 'totalSerie'));
    }


    public function registrarSerialProduto(Request $request)
    {
        // Validação dos dados do formulário
        $request->validate([
            'serial' => 'required|string',
        ], [
            'serial.required' => 'O serial do produto é obrigatório.',
        ]);
    
        // Verifica se o serial já foi coletado para o mesmo produto
        $serialColetado = Coleta::where('sku', session('codigo_produto'))
            ->where('serial', $request->serial)
            ->where('contagem', $this->recount->first()->contagem)
            ->where('grupo', Auth::user()->grupo)
            ->first();

        // Se o serial já foi coletado, retorna com erro
        if ($serialColetado) {
            return redirect()->route('produtoserial')->withErrors(['serial' => "Este serial já foi coletado anteriormente na Área $serialColetado->codigo_palet."]);
        }   

        if ($request->serial === session('codigo_produto') ) {
            return redirect()->route('produtoserial')->withErrors(['serial' => "Código do produto não pode ser seu serial"]);
        }
    
    
        // Caso contrário, registra o serial
        Coleta::create([
            'codigo_palet' => session('codigo_palet'),
            'sku' => session('codigo_produto'),
            'serial' => $request->serial,
            'custo' => session('produto_custo'),
            'status' => 'em_andamento',
            'contagem' => $this->recount->first()->contagem,
            'grupo' => Auth::user()->grupo,
        ]);
    
        // Recupera todos os seriais registrados para o produto
        $seriais = Coleta::where('codigo_palet', session('codigo_palet'))
            ->where('sku', session('codigo_produto'))
            ->where('contagem', $this->recount->first()->contagem)
            ->where('grupo', Auth::user()->grupo)
            ->orderBy('created_at', 'desc')
            ->get();

            $totalSerie = $seriais->count();


        // Retorna à página com os seriais e uma mensagem de sucesso
        return view('serial-produto', compact('seriais', 'totalSerie'))
            ->with('success', 'Serial registrado com sucesso!');
    }

    public function excluirUltimoSerial(Request $request)
    {
        // Validação para garantir que o ID do serial foi enviado
        $request->validate([
            'serial_id' => 'required|exists:coletas,id',
        ]);
    
        // Recupera o serial pelo ID
        $serial = Coleta::find($request->serial_id);
    
        if ($serial) {
            $serial->delete(); // Exclui o serial
            return redirect()->route('produtoserial')->with('success', 'Último serial excluído com sucesso!');
        }
    
        return redirect()->route('produtoserial')->with('error', 'Nenhum serial encontrado para excluir.');
    }

    public function excluirProduto($id)
{
    try {
        DB::beginTransaction();

        // Recupera o produto pelo ID
        $produto = Coleta::findOrFail($id);

        // Verifica se o produto pertence ao grupo do usuário
        if ($produto->grupo !== Auth::user()->grupo) {
            throw new \Exception('Ação não autorizada.');
        }

        // Exclui o produto
        $produto->delete();

        DB::commit();

        // Redireciona de volta para a página de produtos coletados na área
        return redirect()->route('produto')->with('success', 'Produto excluído com sucesso!');
    } catch (\Exception $e) {
        DB::rollBack();
        return redirect()->route('produto')->with('error', $e->getMessage());
    }
}

    public function encerrarProduto()
{
    session()->forget(['codigo_produto', 'produto_custo']);
    return redirect()->route('produto')->with('success', 'Produto encerrado com sucesso!');
}

    public function finalizarColeta()
    {       

        Coleta::where('codigo_palet', session('codigo_palet'))
                ->where('grupo', Auth::user()->grupo)
                ->where('contagem', $this->recount->first()->contagem)
              ->update(['status' => 'finalizada']);

              $status = [
                'total_areas' => Area::count(),
                'areas_contadas' => Area::whereHas('coletas', function($query) {
                    $query->where('contagem', $this->recount->first()->contagem)
                          ->where('status', 'finalizada')
                          ->where('grupo', Auth::user()->grupo);
                })->count(),
                'areas_em_andamento' => Area::whereHas('coletas', function($query) {
                    $query->where('contagem', $this->recount->first()->contagem)
                          ->where('status', 'em_andamento')
                          ->where('grupo', Auth::user()->grupo);
                })->count(),
                'areas_pendentes' => Area::whereDoesntHave('coletas', function($query) {
                    $query->where(function($q) {
                        $q->where('contagem', $this->recount->first()->contagem)
                          ->orWhere('status', 'em_andamento');
                    })
                    ->where('grupo', Auth::user()->grupo);
                })->count()
            ];

            if ($status['areas_pendentes'] == 0 && $status['areas_em_andamento'] == 0) {

                Grupo::where('nome', Auth::user()->grupo)
                            ->update([
                                'status'=> 'finalizada']);
            }
        
        session()->forget(['codigo_palet', 'codigo_produto', 'produto_custo']);
        return redirect()->route('index')->with('success', 'Coleta finalizada com sucesso!');
    }

    public function salvar(Request $request)
{
    $request->validate([
        'sku' => 'required|string',
        'quantidade' => 'required|integer|min:1',
    ]);
    
    $area = session('codigo_palet');

    $produtoExistente2 = Produto::where('codigo_barras', $request->sku)
                                    ->where('ativo', 1)
                                    ->first();


        if (!$produtoExistente2) {
            return redirect()->route('produto')->withErrors(['codigo_produto' => 'Código do produto não encontrado.']);
        }

        $cod_prod = $produtoExistente2->id;


        $prod_cust2 = Custos::where('produto_id',$cod_prod = $produtoExistente2->id)
        ->where('empresa_id', 1)
        ->where('destino_estoque_id', 9)
        ->value('valor_custo_medio');

        if($prod_cust2 === NULL){
            $prod_cust2 = 0.00;
        }
    
    // Loop para inserir de acordo com a quantidade
    for ($i = 0; $i < $request->quantidade; $i++) {
        // Gere um serial único ou outro identificador se necessário
        
        // Insira no banco de dados
        DB::table('coletas')->insert([
            'codigo_palet' => session('codigo_palet'),
                'sku' => $request->sku,
                'status' => 'em_andamento',
                'custo' => session('produto_custo'),
                'contagem' => $this->recount->first()->contagem,
                'grupo' => Auth::user()->grupo,
        ]);
    }
    
    return redirect()->back()->with('success', 'Produto cadastrado com sucesso! Quantidade: ' . $request->quantidade);
}

    public function lista(Request $request)
{
    // Recupera todos os grupos distintos
    $grupos = Coleta::distinct('grupo')->pluck('grupo');

    // Aplica os filtros
    $query = Coleta::query();
    if ($request->filled('contagem')) {
        $query->where('contagem', $request->input('contagem'));
    }
    if ($request->filled('grupo')) {
        $query->where('grupo', $request->input('grupo'));
    }

    // Recupera as coletas filtradas
    $coletas = $query->get();

    // Calcula o total de custo
    $totalCusto = $coletas->sum('custo');

    return view('relatorio_lista', compact('coletas', 'totalCusto', 'grupos'));
}

public function exportarLista($contagem, $grupo)
{
    // Aplica os filtros
    $query = Coleta::query();
    if ($contagem) {
        $query->where('contagem', $contagem);
    }
    if ($grupo) {
        $query->where('grupo', $grupo);
    }

    // Recupera as coletas filtradas
    $coletas = $query->get();

    return Excel::download(new RelatorioListaExport($coletas), 'relatorio_lista.xlsx');
}
}