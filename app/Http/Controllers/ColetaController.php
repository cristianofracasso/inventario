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

        return view('produto', compact('itens'));
    }

    public function exibirFormularioProduto()
    {
       $itens = Coleta::where('codigo_palet', session('codigo_palet') )
                        ->where('grupo', Auth::user()->grupo)
                        ->where('contagem', $this->recount->first()->contagem)
                        ->orderBy('created_at', 'desc')
                        ->get();

        return view('produto', compact('itens'));
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

        

        // Armazena o código do produto na sessão
        session(['codigo_produto' => $request->codigo_produto]);

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
    
        return view('serial-produto', compact('seriais'));
    }
    public function destroy($id)
    {
        // Encontra o registro que está tentando excluir
        $coleta = Coleta::findOrFail($id);
        
        // Busca o último registro para o mesmo código_palet, grupo e contagem
        $ultimoRegistro = Coleta::where('codigo_palet', $coleta->codigo_palet)
            ->where('grupo', Auth::user()->grupo)
            ->where('contagem', $this->recount->first()->contagem)
            ->latest('created_at')
            ->first();
        
        // Verifica se o registro que está tentando excluir é o último
        if ($coleta->id !== $ultimoRegistro->id) {
            if (request()->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Apenas o último registro pode ser excluído.'], 403);
            }
            return redirect()->back()->with('error', 'Apenas o último registro pode ser excluído.');
        }
        
        // Se chegou aqui, é o último registro e pode ser excluído
        $coleta->delete();
        
        if (request()->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Registro excluído com sucesso!'], 200);
        }
        return redirect()->back()->with('success', 'Registro excluído com sucesso!');
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
    
        // Retorna à página com os seriais e uma mensagem de sucesso
        return view('serial-produto', compact('seriais'))
            ->with('success', 'Serial registrado com sucesso!');
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