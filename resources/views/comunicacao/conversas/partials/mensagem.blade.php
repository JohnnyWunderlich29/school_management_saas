@php
    $isPropia = $mensagem->remetente_id === auth()->id();
@endphp

<div class="flex {{ $isPropia ? 'justify-end' : 'justify-start' }} mb-6 group" data-mensagem-id="{{ $mensagem->id }}">
    <div class="flex items-start space-x-3 max-w-xs lg:max-w-md xl:max-w-lg">
        @if(!$isPropia)
            <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-purple-600 rounded-full flex items-center justify-center text-white text-sm font-semibold flex-shrink-0 shadow-lg ring-2 ring-white">
                {{ strtoupper(substr($mensagem->remetente?->name ?? 'U', 0, 1)) }}
            </div>
        @endif
        
        <div class="flex flex-col {{ $isPropia ? 'items-end' : 'items-start' }} space-y-1">
            @if(!$isPropia)
                <div class="text-sm font-medium text-gray-700 mb-1 px-1">
                    {{ $mensagem->remetente?->name ?? 'Usuário removido' }}
                </div>
            @endif
            
            <div class="{{ $isPropia ? 'bg-gradient-to-r from-blue-600 to-purple-600 text-white shadow-lg' : 'bg-white/90 backdrop-blur-sm border border-gray-200/50 shadow-md' }} rounded-2xl px-5 py-3 transition-all duration-200 hover:shadow-lg {{ $isPropia ? 'hover:from-blue-700 hover:to-purple-700' : 'hover:bg-white' }}">
                @if($mensagem->conteudo)
                    <div class="text-sm leading-relaxed">
                        {!! $mensagem->getConteudoSanitizado() !!}
                    </div>
                @endif
                
                @if($mensagem->arquivo_nome)
                    <div class="{{ $mensagem->conteudo ? 'mt-3' : '' }}">
                        @php
                            $extensao = pathinfo($mensagem->arquivo_nome, PATHINFO_EXTENSION);
                            $isImagem = in_array(strtolower($extensao), ['jpg', 'jpeg', 'png', 'gif', 'webp']);
                            $isVideo = in_array(strtolower($extensao), ['mp4', 'avi', 'mov', 'wmv', 'flv']);
                            $isAudio = in_array(strtolower($extensao), ['mp3', 'wav', 'ogg', 'm4a']);
                        @endphp
                        
                        @if($isImagem)
                            <div class="rounded-xl overflow-hidden shadow-lg hover:shadow-xl transition-all duration-300 group">
                                <img src="{{ Storage::url($mensagem->arquivo_path) }}" 
                                     alt="{{ $mensagem->arquivo_nome }}" 
                                     class="max-w-full h-auto rounded-xl cursor-pointer hover:scale-105 transition-transform duration-300" 
                                     style="max-width: 280px; max-height: 220px;"
                                     onclick="abrirImagemModal(this.src, '{{ $mensagem->arquivo_nome }}')">
                            </div>
                        @elseif($isVideo)
                            <div class="rounded-xl overflow-hidden shadow-lg hover:shadow-xl transition-all duration-300">
                                <video controls class="max-w-full h-auto rounded-xl" style="max-width: 280px; max-height: 220px;">
                                    <source src="{{ Storage::url($mensagem->arquivo_path) }}" type="video/{{ $extensao }}">
                                    Seu navegador não suporta o elemento de vídeo.
                                </video>
                            </div>
                        @elseif($isAudio)
                            <div class="bg-gradient-to-r from-gray-100 to-gray-50 rounded-xl p-4 shadow-sm border border-gray-200/50">
                                <audio controls class="w-full" style="max-width: 280px;">
                                    <source src="{{ Storage::url($mensagem->arquivo_path) }}" type="audio/{{ $extensao }}">
                                    Seu navegador não suporta o elemento de áudio.
                                </audio>
                            </div>
                        @else
                            <div class="bg-gradient-to-r from-gray-50 to-blue-50 rounded-xl p-4 border border-gray-200/50 shadow-sm hover:shadow-md transition-all duration-200">
                                <a href="{{ Storage::url($mensagem->arquivo_path) }}" 
                                   target="_blank" 
                                   class="flex items-center space-x-3 text-blue-600 hover:text-blue-800 transition-colors group">
                                   <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-purple-600 rounded-lg flex items-center justify-center text-white shadow-lg group-hover:scale-105 transition-transform">
                                       <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                           <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                       </svg>
                                   </div>
                                   <div class="flex flex-col">
                                       <span class="text-sm font-medium truncate">{{ Str::limit($mensagem->arquivo_nome, 25) }}</span>
                                       @if($mensagem->arquivo_tamanho)
                                           <small class="text-gray-500">{{ $mensagem->formatarTamanhoArquivo() }}</small>
                                       @endif
                                   </div>
                                </a>
                            </div>
                        @endif
                    </div>
                @endif
            </div>
            
            @if($mensagem->importante)
                <div class="mt-2 flex {{ $isPropia ? 'justify-end' : 'justify-start' }}">
                    <div class="flex items-center space-x-1 bg-gradient-to-r from-yellow-400 to-orange-400 text-white px-2 py-1 rounded-full text-xs font-medium shadow-lg">
                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20" title="Mensagem importante">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                        </svg>
                        <span>Importante</span>
                    </div>
                </div>
            @endif
            
            <div class="text-xs text-gray-400 mt-2 {{ $isPropia ? 'text-right' : 'text-left' }} flex items-center {{ $isPropia ? 'justify-end' : 'justify-start' }} space-x-1 opacity-0 group-hover:opacity-100 transition-opacity duration-200">
                <span>{{ $mensagem->created_at->format('d/m/Y H:i') }}</span>
                @if($mensagem->editada_em)
                    <div class="flex items-center space-x-1 text-gray-400" title="Editada em {{ $mensagem->editada_em->format('d/m/Y H:i') }}">
                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z"></path>
                        </svg>
                        <span class="text-xs">editada</span>
                    </div>
                @endif
                @if($isPropia)
                    <span class="ml-2">
                        @php
                            $leituras = $mensagem->leituras()->where('user_id', '!=', auth()->id())->count();
                            $totalParticipantes = $mensagem->conversa->participantes()->where('user_id', '!=', auth()->id())->count();
                        @endphp
                        @if($leituras > 0)
                            <svg class="w-3 h-3 inline text-blue-500" fill="currentColor" viewBox="0 0 20 20" title="Lida por {{ $leituras }} de {{ $totalParticipantes }} participantes">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L4 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                            </svg>
                        @else
                            <svg class="w-3 h-3 inline text-gray-400" fill="currentColor" viewBox="0 0 20 20" title="Enviada">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                            </svg>
                        @endif
                    </span>
                @endif
            </div>
        </div>
        
        @if($isPropia)
            <div class="w-8 h-8 bg-gradient-to-br from-blue-500 to-blue-600 rounded-full flex items-center justify-center text-white text-sm font-semibold flex-shrink-0 ml-3">
                {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
            </div>
        @endif
    </div>
</div>
<!-- Modal para visualizar imagens -->
<div class="modal fade" id="imagemModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="imagemModalLabel">Visualizar Imagem</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center">
                <img id="imagemModalImg" src="" alt="" class="img-fluid">
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function abrirImagemModal(src, nome) {
    document.getElementById('imagemModalImg').src = src;
    document.getElementById('imagemModalLabel').textContent = nome;
    new bootstrap.Modal(document.getElementById('imagemModal')).show();
}
</script>
@endpush
