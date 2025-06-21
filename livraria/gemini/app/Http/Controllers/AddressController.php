<?php

namespace App\Http\Controllers;

use App\Models\UserAddress;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AddressController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Lista os endereços do usuário
     */
    public function index()
    {
        $addresses = Auth::user()->addresses()->orderBy('is_default', 'desc')
                                             ->orderBy('created_at', 'desc')
                                             ->get();

        return view('perfil.enderecos.index', compact('addresses'));
    }

    /**
     * Formulário para novo endereço
     */
    public function create()
    {
        return view('perfil.enderecos.create');
    }

    /**
     * Armazena novo endereço
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'label' => 'required|string|max:50',
            'recipient_name' => 'required|string|max:100',
            'street' => 'required|string|max:255',
            'number' => 'required|string|max:20',
            'complement' => 'nullable|string|max:100',
            'neighborhood' => 'required|string|max:100',
            'city' => 'required|string|max:100',
            'state' => 'required|string|size:2',
            'postal_code' => 'required|string|regex:/^\d{5}-?\d{3}$/',
            'reference' => 'nullable|string|max:255',
            'is_default' => 'boolean',
        ]);

        $data['user_id'] = Auth::id();
        $data['is_default'] = $request->boolean('is_default');

        // Limpar CEP
        $data['postal_code'] = preg_replace('/\D/', '', $data['postal_code']);

        $address = UserAddress::create($data);

        return redirect()->route('perfil.enderecos.index')
                        ->with('success', 'Endereço adicionado com sucesso!');
    }

    /**
     * Exibe um endereço específico
     */
    public function show(UserAddress $endereco)
    {
        $this->authorize('view', $endereco);
        return view('perfil.enderecos.show', compact('endereco'));
    }

    /**
     * Formulário de edição
     */
    public function edit(UserAddress $endereco)
    {
        $this->authorize('update', $endereco);
        return view('perfil.enderecos.edit', compact('endereco'));
    }

    /**
     * Atualiza o endereço
     */
    public function update(Request $request, UserAddress $endereco)
    {
        $this->authorize('update', $endereco);

        $data = $request->validate([
            'label' => 'required|string|max:50',
            'recipient_name' => 'required|string|max:100',
            'street' => 'required|string|max:255',
            'number' => 'required|string|max:20',
            'complement' => 'nullable|string|max:100',
            'neighborhood' => 'required|string|max:100',
            'city' => 'required|string|max:100',
            'state' => 'required|string|size:2',
            'postal_code' => 'required|string|regex:/^\d{5}-?\d{3}$/',
            'reference' => 'nullable|string|max:255',
            'is_default' => 'boolean',
        ]);

        $data['is_default'] = $request->boolean('is_default');
        $data['postal_code'] = preg_replace('/\D/', '', $data['postal_code']);

        $endereco->update($data);

        return redirect()->route('perfil.enderecos.index')
                        ->with('success', 'Endereço atualizado com sucesso!');
    }

    /**
     * Remove o endereço
     */
    public function destroy(UserAddress $endereco)
    {
        $this->authorize('delete', $endereco);

        if (Auth::user()->addresses()->count() <= 1) {
            return redirect()->route('perfil.enderecos.index')
                           ->with('error', 'Você deve ter pelo menos um endereço cadastrado.');
        }

        $endereco->delete();

        return redirect()->route('perfil.enderecos.index')
                        ->with('success', 'Endereço removido com sucesso!');
    }

    /**
     * Define um endereço como padrão (AJAX)
     */
    public function setDefault(UserAddress $endereco)
    {
        $this->authorize('update', $endereco);

        $endereco->setAsDefault();

        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Endereço padrão atualizado com sucesso!'
            ]);
        }

        return redirect()->route('perfil.enderecos.index')
                        ->with('success', 'Endereço padrão atualizado!');
    }

    /**
     * Busca CEP via API (AJAX)
     */
    public function buscarCep(Request $request)
    {
        $request->validate([
            'cep' => 'required|string|regex:/^\d{5}-?\d{3}$/'
        ]);

        $cep = preg_replace('/\D/', '', $request->cep);

        try {
            // Usar ViaCEP API
            $response = file_get_contents("https://viacep.com.br/ws/{$cep}/json/");
            $data = json_decode($response, true);

            if (isset($data['erro'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'CEP não encontrado.'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'street' => $data['logradouro'],
                    'neighborhood' => $data['bairro'],
                    'city' => $data['localidade'],
                    'state' => $data['uf'],
                    'postal_code' => $data['cep'],
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao buscar CEP. Tente novamente.'
            ], 500);
        }
    }

    /**
     * Retorna endereços do usuário para AJAX (checkout)
     */
    public function getAddresses()
    {
        $addresses = Auth::user()->addresses()
                              ->orderBy('is_default', 'desc')
                              ->orderBy('label')
                              ->get();

        return response()->json([
            'success' => true,
            'addresses' => $addresses->map(function($address) {
                return [
                    'id' => $address->id,
                    'label' => $address->label,
                    'recipient_name' => $address->recipient_name,
                    'full_address' => $address->full_address,
                    'is_default' => $address->is_default,
                    'data' => $address->toCheckoutArray()
                ];
            })
        ]);
    }