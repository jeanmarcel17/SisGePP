<?php

namespace App\Http\Controllers;

use App\Models\Raca;
use App\Models\Animal;
use App\Models\Origem;


use App\Models\Usuario;
use App\Models\GrauSangue;
use App\Models\Propriedade;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\ClassificacaoEtaria;
use Illuminate\Support\Facades\Validator;
use MigrationsGenerator\Generators\MigrationConstants\Method\Foreign;


class AnimalController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $lstraca = Raca::get()->sortBy('nome');
        $lstorigem = Origem::get()->sortByDesc('nome');
        $lstgrausangue = GrauSangue::get();
        return view('painel.rebanho.animal', compact('lstraca', 'lstorigem', 'lstgrausangue'));
    }

    public function listraca()
    {
        $lstraca = Raca::all();
        return response()->json([
            'lstraca' => $lstraca,
        ]);
    }

    public function listorigem()
    {
        $lstorigem = Origem::all();
        return response()->json([
            'lstorigem' => $lstorigem,
        ]);
    }

    /**
     * Undocumented function
     *
     * @return void
     */
    public function read()
    {
        $animal = Animal::all();
        return response()->json([
            'animal' => $animal,
        ]);
    }

    /**
     * Cria um novo animal
     *
     * @param Request $request
     * @return void Json
     */
    public function create(Request $request)
    {

        // Validação
        $validator = Validator::make($request->all(), [

            // Campos obrigatórios
            'numero_brinco' => ['required', 'string', 'max:15'],
            'nome' => ['required', 'string', 'max:25'],
            'genero' => 'required',
            'dias_vida' => 'required|integer|min:1|max:10000',
            'data_entrada' => 'required',
            'data_nascimento' => 'required',
            'peso_entrada' => 'required|integer|min:1|max:1000',

            // Foreign Keys
            'grau_sangue_idgrau_sangue' => 'required',
            'raca_idraca' => 'required',
            'origem_idorigem' => 'required',

        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 400,
                'errors' => $validator->getMessageBag()->getMessages(),
            ]);
        } else {

            //Propriedade ID e Facha Etaria ID
            $fxetaria = $this->classificacao_etaria($request->input('dias_vida'));
            $propid = Usuario::find(auth()->user()->id)->propriedade_idpropriedade;

            //Verifica se o brinco pertence a outro animal
            $animal = $this->animal_brinco($request->input('numero_brinco'));

            if ($animal) {
                return response()->json([
                    'status' => 100,
                    'message' => 'Este Animal já foi cadastrado.'
                ]);
            }

            // Campos obrigatórios
            $animal = new Animal;
            $animal->nome = $request->input('nome');
            $animal->genero = $request->input('genero');
            $animal->dias_vida = $request->input('dias_vida');
            $animal->numero_brinco = $request->input('numero_brinco');

            $animal->peso_entrada = $request->input('peso_entrada');
            $animal->apelido = $request->input('apelido');
            $animal->foto = $request->input('foto');
            $animal->numero_sisbov = $request->input('numero_sisbov');
            $animal->observacao = $request->input('observacao');
            $animal->rgd = $request->input('rgd');
            $animal->rgn = $request->input('rgn');
            $animal->data_entrada = $request->input('data_entrada');
            $animal->data_nascimento = $request->input('data_nascimento');
            $animal->ativo = 1;

            // Foreign Keys
            $animal->propriedade_idpropriedade = $propid;
            $animal->raca_idraca = $request->input('raca_idraca');
            $animal->grau_sangue_idgrau_sangue = $request->input('grau_sangue_idgrau_sangue');
            $animal->origem_idorigem = $request->input('origem_idorigem');
            $animal->lote_idlote = 1;

            try {
                $animal->save();
                return response()->json([
                    'status' => 200,
                    'message' => 'Novo Animal salvo com sucesso.'
                ]);
            } catch (\Exception $e) {
                return response()->json([
                    'status' => 503,
                    'message' => 'Não foi possível cadastrar esse novo animal. Tente novamente.' //$e->getMessage()
                ]);
            }
        }
    }

    /**
     * Consulta a faixa etária em que o animal se encontra atualmente
     *
     * @param [type] $diasvida
     * @return Array Classificação Etária
     */
    private function classificacao_etaria($diasvida)
    {
        $fxetaria = DB::select(DB::raw("SELECT idclassificacao_etaria FROM classificacao_etaria WHERE :diasvida BETWEEN dia_inicial AND dia_final"), array(
            'diasvida' => $diasvida,
        ));
        return ($fxetaria);
    }

    private function animal_brinco($numbrinco)
    {
        //Verifica se o brinco pertence a outro animal
        $animal = DB::select(DB::raw("SELECT * FROM animal WHERE numero_brinco = :numbrinco AND ativo = :ativo"), array(
            'numbrinco' => $numbrinco,
            'ativo' => 1,
        ));
        return ($animal);
    }

}
