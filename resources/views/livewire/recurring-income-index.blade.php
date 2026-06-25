<div>
    <div class="flex justify-end mb-6">
        <button class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-green-600 text-white text-[13px] font-semibold rounded-lg hover:bg-green-700 transition-colors">+ Add Recurring Income</button>
    </div>
    <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200">
                        <th class="text-left px-5 py-3 text-[10.5px] font-bold uppercase tracking-widest text-slate-400">Name</th>
                        <th class="text-left px-5 py-3 text-[10.5px] font-bold uppercase tracking-widest text-slate-400 hidden sm:table-cell">Frequency</th>
                        <th class="text-left px-5 py-3 text-[10.5px] font-bold uppercase tracking-widest text-slate-400">Amount</th>
                        <th class="text-left px-5 py-3 text-[10.5px] font-bold uppercase tracking-widest text-slate-400 hidden md:table-cell">End Date</th>
                        <th class="px-5 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($rules as $rule)
                    <tr class="hover:bg-slate-50/50 group">
                        <td class="px-5 py-3.5">
                            <div class="flex items-center gap-2.5">
                                <div class="w-8 h-8 rounded-lg bg-blue-50 flex items-center justify-center text-[12px] font-bold text-blue-600">{{ strtoupper(substr($rule->name, 0, 1)) }}</div>
                                <div class="text-[13.5px] font-semibold text-slate-900">{{ $rule->name }}</div>
                            </div>
                        </td>
                        <td class="px-5 py-3.5 hidden sm:table-cell"><span class="inline-flex px-2.5 py-0.5 rounded-full text-[11.5px] font-medium bg-blue-50 text-blue-600">{{ $rule->frequency->name }}</span></td>
                        <td class="px-5 py-3.5 font-mono text-[13px] font-medium text-blue-600">+${{ number_format($rule->amount, 2) }}</td>
                        <td class="px-5 py-3.5 font-mono text-[12.5px] text-slate-400 hidden md:table-cell">{{ $rule->end_date->format('d M Y') }}</td>
                        <td class="px-5 py-3.5"><div class="flex items-center gap-1 justify-end opacity-0 group-hover:opacity-100 transition-opacity"><button class="w-8 h-8 flex items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-400 hover:bg-slate-50 hover:text-slate-700 transition-all text-sm">✏️</button><button class="w-8 h-8 flex items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-400 hover:bg-red-50 hover:text-red-600 hover:border-red-200 transition-all text-sm">🗑</button></div></td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="px-5 py-10 text-center text-slate-400 text-[13px]">No recurring income yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
