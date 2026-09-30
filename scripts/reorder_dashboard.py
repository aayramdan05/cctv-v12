import sys

with open(r'd:\Unpad\CCTV Monitoring\cctv-v12\resources\views\dashboard.blade.php', 'r') as f:
    lines = f.readlines()

with open(r'd:\Unpad\CCTV Monitoring\cctv-v12\scratch_ui.txt', 'r') as f:
    new_ui = f.readlines()

old_campus = '''                <div id="campus-map" class="bg-white/70 backdrop-blur-md border border-white/30 shadow-sm rounded-2xl p-6">
                <div class="flex items-center justify-between mb-6">
                    <div class="flex items-center gap-3">
                        <div class="p-2 bg-cyan-50 rounded-lg text-cyan-600">
                            <i class="fas fa-map-marked-alt text-lg"></i>
                        </div>
                        <h3 class="text-xl font-bold text-slate-800">Campus Overview</h3>
                    </div>
                    <a href="{{ route('building.index') }}" class="text-xs text-cyan-600 font-bold hover:text-cyan-700 hover:underline transition-colors">
                        View All
                    </a>
                </div>
                
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($buildings as $building)
                    <div onclick="location.href='{{ route('monitoring.index', ['building_id' => $building->id]) }}'"
                         class="bg-white border border-slate-100 rounded-xl p-4 hover:border-cyan-300 hover:shadow-md transition-all group cursor-pointer">
                        <div class="flex items-start justify-between mb-3">
                            <div class="w-10 h-10 rounded-lg bg-slate-50 border border-slate-200 flex items-center justify-center text-slate-400 group-hover:bg-cyan-50 group-hover:text-cyan-600 group-hover:border-cyan-200 transition-colors">
                                <i class="fas fa-building text-lg"></i>
                            </div>
                            <span class="px-2 py-1 rounded-md bg-green-50 text-green-600 text-[10px] font-bold border border-green-100 uppercase tracking-wide">
                                Online
                            </span>
                        </div>
                        
                        <div class="mb-3">
                            <h4 class="font-bold text-slate-800 text-sm truncate" title="{{ $building->nama_gedung }}">
                                {{ $building->nama_gedung }}
                            </h4>
                            <div class="flex items-center gap-2 text-xs text-slate-500 mt-1">
                                <i class="fas fa-video text-slate-300"></i>
                                <span>{{ $building->cctvs_count }} Cameras</span>
                            </div>
                        </div>

                        <div class="pt-3 border-t border-slate-50 flex items-center justify-between text-xs">
                            <span class="text-slate-400 font-medium truncate max-w-[120px]" title="{{ $building->fakultas }}">
                                {{ $building->fakultas }}
                            </span>
                            <i class="fas fa-chevron-right text-slate-300 group-hover:text-cyan-500 transition-colors"></i>
                        </div>
                    </div>
                    @endforeach
                </div>
                </div>\n'''

new_ui_start = -1
new_ui_end = -1
for i, line in enumerate(lines):
    if '<div class="grid grid-cols-1 xl:grid-cols-5 gap-6">' in line and new_ui_start == -1:
        new_ui_start = i
    if '<div id="recent-alerts"' in line:
        new_ui_end = i - 2
        break

lines = lines[:new_ui_start] + [old_campus] + lines[new_ui_end:]

main_grid_end = -1
for i, line in enumerate(lines):
    if '<div id="live-feeds-section"' in line:
        main_grid_end = i - 1
        break

new_ui_str = ''.join(new_ui)
new_ui_str = new_ui_str.replace('<div class="grid grid-cols-1 xl:grid-cols-5 gap-6">', '<div class="grid grid-cols-1 xl:grid-cols-5 gap-6 mb-8">')
new_ui_str = new_ui_str.replace('max-w-[120px]', 'max-w-[180px]') # Make more room for building title
new_ui_str = new_ui_str.replace('text-[13px] font-extrabold', 'text-[14px] font-extrabold') # Increase title sizes slightly

lines = lines[:main_grid_end] + [new_ui_str] + lines[main_grid_end:]

with open(r'd:\Unpad\CCTV Monitoring\cctv-v12\resources\views\dashboard.blade.php', 'w') as f:
    f.writelines(lines)
