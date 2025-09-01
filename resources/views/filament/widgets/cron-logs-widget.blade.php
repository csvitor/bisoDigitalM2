<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-blue-100 dark:bg-blue-900/20">
                        <x-filament::icon icon="heroicon-o-clock" class="h-5 w-5 text-blue-600 dark:text-blue-400" />
                    </div>
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Status dos Crons de Sincronização</h2>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Monitoramento em tempo real dos processos agendados</p>
                    </div>
                </div>
                @if($hasData && count($crons) > 0)
                    <div class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400">
                        <x-filament::icon icon="heroicon-o-arrow-path" class="h-4 w-4" />
                        <span>Atualizado agora</span>
                    </div>
                @endif
            </div>
        </x-slot>

        @if($hasData && count($crons) > 0)
            <div class="grid gap-4 grid-cols-1 lg:grid-cols-2 xl:grid-cols-3">
                @foreach($crons as $cron)
                    <div class="group relative overflow-hidden rounded-lg border border-gray-200 bg-white p-4 shadow-sm transition-all duration-200 hover:shadow-md hover:border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:hover:border-gray-600">
                        <!-- Header do card -->
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex items-center gap-3">
                                <div class="flex h-10 w-10 items-center justify-center rounded-lg
                                    @if($cron['color'] === 'success') bg-green-100 dark:bg-green-900/20
                                    @elseif($cron['color'] === 'warning') bg-yellow-100 dark:bg-yellow-900/20
                                    @elseif($cron['color'] === 'danger') bg-red-100 dark:bg-red-900/20
                                    @else bg-gray-100 dark:bg-gray-700
                                    @endif">
                                    <x-filament::icon 
                                        :icon="$cron['icon']" 
                                        class="h-5 w-5
                                            @if($cron['color'] === 'success') text-green-600 dark:text-green-400
                                            @elseif($cron['color'] === 'warning') text-yellow-600 dark:text-yellow-400
                                            @elseif($cron['color'] === 'danger') text-red-600 dark:text-red-400
                                            @else text-gray-600 dark:text-gray-400
                                            @endif"
                                    />
                                </div>
                                <div>
                                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white">
                                        {{ $cron['name'] }}
                                    </h3>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">
                                        {{ $cron['status'] }}
                                    </p>
                                </div>
                            </div>
                            
                            <!-- Status Badge -->
                            <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-medium
                                @if($cron['color'] === 'success') bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-100
                                @elseif($cron['color'] === 'warning') bg-yellow-100 text-yellow-800 dark:bg-yellow-800 dark:text-yellow-100
                                @elseif($cron['color'] === 'danger') bg-red-100 text-red-800 dark:bg-red-800 dark:text-red-100
                                @else bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-100
                                @endif">
                                <div class="w-1.5 h-1.5 rounded-full
                                    @if($cron['color'] === 'success') bg-green-500
                                    @elseif($cron['color'] === 'warning') bg-yellow-500
                                    @elseif($cron['color'] === 'danger') bg-red-500
                                    @else bg-gray-400
                                    @endif">
                                </div>
                                @if($cron['color'] === 'success') Ativo
                                @elseif($cron['color'] === 'warning') Atenção
                                @elseif($cron['color'] === 'danger') Erro
                                @else Inativo
                                @endif
                            </span>
                        </div>
                        
                        <!-- Informações organizadas -->
                        <div class="space-y-3">
                            <!-- Última Execução -->
                            <div>
                                <div class="flex items-center gap-2 mb-1">
                                    <x-filament::icon icon="heroicon-o-clock" class="h-3.5 w-3.5 text-gray-400" />
                                    <span class="text-xs font-medium text-gray-600 dark:text-gray-400 uppercase tracking-wide">Última Execução</span>
                                </div>
                                @if($cron['last_execution'])
                                    <p class="text-sm font-mono text-gray-900 dark:text-white pl-5">
                                        {{ \Carbon\Carbon::parse($cron['last_execution'])->format('d/m H:i') }}
                                    </p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 pl-5">
                                        {{ \Carbon\Carbon::parse($cron['last_execution'])->diffForHumans() }}
                                    </p>
                                @else
                                    <p class="text-sm text-gray-500 dark:text-gray-400 pl-5">Nunca executado</p>
                                @endif
                            </div>
                            
                            <!-- Próxima Execução -->
                            <div>
                                <div class="flex items-center gap-2 mb-1">
                                    <x-filament::icon icon="heroicon-o-forward" class="h-3.5 w-3.5 text-gray-400" />
                                    <span class="text-xs font-medium text-gray-600 dark:text-gray-400 uppercase tracking-wide">Próxima Execução</span>
                                </div>
                                @if($cron['next_execution'])
                                    <p class="text-sm font-mono text-gray-900 dark:text-white pl-5">
                                        {{ \Carbon\Carbon::parse($cron['next_execution'])->format('d/m H:i') }}
                                    </p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 pl-5">
                                        {{ \Carbon\Carbon::parse($cron['next_execution'])->diffForHumans() }}
                                    </p>
                                @else
                                    <p class="text-sm text-gray-500 dark:text-gray-400 pl-5">Não agendado</p>
                                @endif
                            </div>
                            
                            <!-- Agendamento -->
                            <div>
                                <div class="flex items-center gap-2 mb-1">
                                    <x-filament::icon icon="heroicon-o-calendar" class="h-3.5 w-3.5 text-gray-400" />
                                    <span class="text-xs font-medium text-gray-600 dark:text-gray-400 uppercase tracking-wide">Agendamento</span>
                                </div>
                                <p class="text-sm font-mono text-gray-900 dark:text-white pl-5">
                                    {{ $cron['schedule_description'] ?? $cron['schedule'] }}
                                </p>
                                <p class="text-xs text-gray-500 dark:text-gray-400 pl-5">
                                    Frequência de execução
                                </p>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="text-center py-12">
                <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-gray-100 dark:bg-gray-800">
                    <x-filament::icon 
                        icon="heroicon-o-exclamation-triangle" 
                        class="h-8 w-8 text-gray-400"
                    />
                </div>
                <h3 class="mt-4 text-lg font-semibold text-gray-900 dark:text-white">
                    Nenhuma configuração encontrada
                </h3>
                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400 max-w-md mx-auto">
                    Configure os crons na seção de configurações para visualizar o status dos processos de sincronização.
                </p>
                <div class="mt-6">
                    <button type="button" class="inline-flex items-center gap-2 rounded-md bg-blue-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-gray-900">
                        <x-filament::icon icon="heroicon-o-cog-6-tooth" class="h-4 w-4" />
                        Configurar Crons
                    </button>
                </div>
            </div>
        @endif
    </x-filament::section>
</x-filament-widgets::widget>
