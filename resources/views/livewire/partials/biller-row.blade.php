<tr class="hover:bg-slate-50/50 transition-colors group border-t border-slate-100">
    <td class="px-5 py-3.5">
        <div class="flex items-center gap-2.5">
            <div class="w-8 h-8 rounded-lg flex items-center justify-center text-[12px] font-bold flex-shrink-0"
                 style="background: hsl({{ crc32($biller->name) % 360 }}, 70%, 92%); color: hsl({{ crc32($biller->name) % 360 }}, 60%, 35%)">
                {{ strtoupper(substr($biller->name, 0, 1)) }}
            </div>
            <div class="text-[13.5px] font-semibold text-slate-900">{{ $biller->name }}</div>
        </div>
    </td>
    <td class="px-5 py-3.5 text-[13px] text-slate-500 hidden sm:table-cell">{{ $biller->email ?? '—' }}</td>
    <td class="px-5 py-3.5 text-[13px] text-slate-500 hidden md:table-cell">{{ $biller->phone ?? '—' }}</td>
    <td class="px-5 py-3.5 text-right">
        <span class="font-mono text-[13px] font-semibold {{ $biller->payments_sum_amount > 0 ? 'text-slate-900' : 'text-slate-300' }}">
            ${{ number_format($biller->payments_sum_amount ?? 0, 2) }}
        </span>
    </td>
    <td class="px-5 py-3.5">
        <div class="flex items-center gap-1 justify-end lg:opacity-0 lg:group-hover:opacity-100 transition-opacity">
            <button class="w-8 h-8 flex items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-400 hover:bg-slate-50 hover:text-slate-700 transition-all text-sm">✏️</button>
            <button class="w-8 h-8 flex items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-400 hover:bg-red-50 hover:text-red-600 hover:border-red-200 transition-all text-sm">🗑</button>
        </div>
    </td>
</tr>
