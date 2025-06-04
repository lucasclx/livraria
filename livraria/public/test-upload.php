public function getImagemUrlAttribute()
{
    if ($this->imagem) {
        // Verificar se existe na pasta public/images/livros
        $publicPath = public_path('images/livros/' . $this->imagem);
        if (file_exists($publicPath)) {
            return asset('images/livros/' . $this->imagem);
        }
        
        // Verificar se existe no storage
        if (Storage::exists('public/livros/' . $this->imagem)) {
            return Storage::url('livros/' . $this->imagem);
        }
    }
    
    return asset('images/no-book.png');
}