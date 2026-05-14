<div class="space-y-4">
    @if ($exports->isEmpty())
        <p class="text-sm text-gray-500 dark:text-gray-400">
            Nenhuma exportação foi solicitada ainda.
        </p>
    @else
        <div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-white/10">
            <table class="w-full min-w-[720px] divide-y divide-gray-200 text-left text-sm dark:divide-white/10">
                <thead class="bg-gray-50 text-xs font-semibold uppercase tracking-wide text-gray-500 dark:bg-white/5 dark:text-gray-400">
                    <tr>
                        <th class="px-3 py-2">Arquivo</th>
                        <th class="px-3 py-2">Status</th>
                        <th class="px-3 py-2">Linhas</th>
                        <th class="px-3 py-2">Solicitada em</th>
                        <th class="px-3 py-2 text-right">Ação</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-white/10">
                    @foreach ($exports as $export)
                        <tr>
                            <td class="px-3 py-3 font-medium text-gray-950 dark:text-white">
                                {{ $export->file_name }}
                            </td>
                            <td class="px-3 py-3">
                                <span @class([
                                    'inline-flex rounded-md px-2 py-1 text-xs font-medium',
                                    'bg-gray-100 text-gray-700 dark:bg-gray-500/20 dark:text-gray-300' => $export->status->value === 'pending',
                                    'bg-amber-100 text-amber-700 dark:bg-amber-500/20 dark:text-amber-300' => $export->status->value === 'processing',
                                    'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-300' => $export->status->value === 'completed',
                                    'bg-red-100 text-red-700 dark:bg-red-500/20 dark:text-red-300' => $export->status->value === 'failed',
                                ])>
                                    {{ $export->status->getLabel() }}
                                </span>
                            </td>
                            <td class="px-3 py-3 text-gray-700 dark:text-gray-300">
                                {{ number_format($export->rows_processed, 0, ',', '.') }}
                            </td>
                            <td class="px-3 py-3 text-gray-700 dark:text-gray-300">
                                {{ $export->created_at?->format('d/m/Y H:i') }}
                            </td>
                            <td class="px-3 py-3 text-right">
                                @if ($export->isCompleted())
                                    <a
                                        href="{{ route('student-exports.download', $export) }}"
                                        target="_blank"
                                        class="text-sm font-semibold text-primary-600 hover:text-primary-500 dark:text-primary-400"
                                    >
                                        Baixar
                                    </a>
                                @elseif ($export->status->value === 'failed')
                                    <span class="text-sm text-gray-500 dark:text-gray-400">Ver logs</span>
                                @else
                                    <span class="text-sm text-gray-500 dark:text-gray-400">Em processamento</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
