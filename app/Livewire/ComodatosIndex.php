<?php
namespace App\Livewire;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Livewire\Component;
use Livewire\WithPagination;
class ComodatosIndex extends Component {
 use WithPagination;
 public string $equipoId='', $solicitante='', $escuela='', $numero='', $inicio='', $fin='', $buscar='';
 public function mount(): void { Gate::authorize('gestionar'); $this->inicio=now()->toDateString(); }
 public function guardar(): void {
 Gate::authorize('gestionar');
 $this->validate(['equipoId'=>'required|integer','solicitante'=>'required|string|max:255','escuela'=>'required|string|max:255','numero'=>'required|string|max:255','inicio'=>'required|date_format:Y-m-d','fin'=>'required|date_format:Y-m-d|after_or_equal:inicio']);
 DB::transaction(function(){
 $e=DB::table('equipos')->where('id_equipo',$this->equipoId)->lockForUpdate()->first();
 if(!$e || !DB::table('tipos_equipo')->where('id_tipo_equipo',$e->id_tipo_equipo)->where('clasificacion_bien','global')->exists() || DB::table('bajas')->where('id_equipo',$this->equipoId)->exists() || DB::table('bajas_resguardo')->where('id_equipo',$this->equipoId)->whereNull('fecha_completada')->exists()) throw ValidationException::withMessages(['equipoId'=>'Selecciona un mueble o equipo vigente y sin baja en resguardo.']);
 if(DB::table('comodatos')->where('id_equipo',$this->equipoId)->whereDate('inicio','<=',$this->fin)->whereDate('fin','>=',$this->inicio)->exists()) throw ValidationException::withMessages(['equipoId'=>'Este bien ya tiene un comodato en ese periodo.']);
 DB::table('comodatos')->insert(['id_equipo'=>$this->equipoId,'solicitante'=>trim($this->solicitante),'escuela'=>trim($this->escuela),'numero'=>trim($this->numero),'inicio'=>$this->inicio,'fin'=>$this->fin,'registrado_por'=>auth()->id(),'created_at'=>now(),'updated_at'=>now()]);
 DB::table('bitacora_auditoria')->insert(['id_usuario'=>auth()->id(),'accion'=>'COMODATO_REGISTRADO','modulo'=>'MOVIMIENTOS','descripcion'=>'Comodato '.$this->numero.' del bien '.$e->codigo_inventario,'fecha_hora'=>now()]);
 });
 $this->reset('equipoId','solicitante','escuela','numero','fin'); session()->flash('comodato_ok','Comodato registrado.');
 }
 public function render(){ Gate::authorize('gestionar');
 $equipos=DB::table('equipos as e')->join('tipos_equipo as t','t.id_tipo_equipo','=','e.id_tipo_equipo')->where('t.clasificacion_bien','global')->whereNotExists(fn($q)=>$q->selectRaw('1')->from('bajas')->whereColumn('bajas.id_equipo','e.id_equipo'))->orderBy('e.nombre_equipo')->get(['e.id_equipo','e.nombre_equipo','e.codigo_inventario']);
 $registros=DB::table('comodatos as c')->join('equipos as e','e.id_equipo','=','c.id_equipo')->select('c.*','e.nombre_equipo','e.codigo_inventario')->orderByDesc('c.id')->paginate(20);
 return view('livewire.comodatos-index',compact('equipos','registros')); }
}
