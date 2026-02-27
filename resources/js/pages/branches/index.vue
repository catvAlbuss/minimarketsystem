<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Head } from '@inertiajs/vue3'
import { ref, reactive, computed, watch } from 'vue'
import Chart from 'chart.js/auto'
import { 
  Computer, Plus, ArrowLeft, Download, UserPlus, 
  DollarSign, Clock, MapPin, Phone, X
} from 'lucide-vue-next'

// Types y datos 
type Branch = {
  id: number
  codigo: string
  nombre: string
  ruc: string
  serie: string
  licencia: string
  direccion: string
  horario: string
  encargado: string
  telefono: string
  estado: string
  ventasHoy: number
  ventasMensual: number
  ticketPromedio: number
  tasaMerma: number
  cajaApertura: number
  cajaActual: number
  personal: any[]
  stock: any[]
  ventasSemanales: number[]
  productosEstrella: { nombre: string; ventas: number }[]
  cajaMovimientos: any[]
}

const props = defineProps<{ branches?: Branch[] }>()

// Datos de ejemplo (simulan la respuesta del backend)
const sucursalesDB = ref<Branch[]>([
  {
    id: 1,
    codigo: "SUC-01-AMA",
    nombre: "NetMarket Amarillis",
    ruc: "20555666777",
    serie: "F001",
    licencia: "Vigente",
    direccion: "Av. Universitaria 123, Amarillis",
    horario: "08:00 AM - 10:00 PM",
    encargado: "Juan Pérez",
    telefono: "987654321",
    estado: "Abierto",
    ventasHoy: 723.3,
    ventasMensual: 6120.2,
    ticketPromedio: 25.5,
    tasaMerma: 2.3,
    cajaApertura: 500,
    cajaActual: 1245.5,
    personal: [
      { nombre: "Juan Pérez", cargo: "Encargado", contacto: "987654321", turno: "Mañana", estado: "Activo" },
      { nombre: "María López", cargo: "Cajera", contacto: "912345678", turno: "Mañana", estado: "Activo" },
      { nombre: "Carlos Ruiz", cargo: "Reponedor", contacto: "923456789", turno: "Tarde", estado: "Activo" }
    ],
    stock: [
      { producto: "Arroz Costeño 1kg", actual: 45, minimo: 50, estado: "Bajo", reposicion: "15/02/2026" },
      { producto: "Leche Gloria 1L", actual: 120, minimo: 100, estado: "Normal", reposicion: "14/02/2026" },
      { producto: "Aceite Primor 1L", actual: 30, minimo: 40, estado: "Bajo", reposicion: "13/02/2026" },
      { producto: "Coca Cola 3L", actual: 85, minimo: 50, estado: "Normal", reposicion: "15/02/2026" }
    ],
    ventasSemanales: [650, 720, 580, 890, 950, 1200, 850],
    productosEstrella: [
      { nombre: "Arroz Costeño", ventas: 145 },
      { nombre: "Leche Gloria", ventas: 132 },
      { nombre: "Coca Cola 3L", ventas: 98 },
      { nombre: "Pan Bimbo", ventas: 87 },
      { nombre: "Huevos (30 un)", ventas: 76 }
    ],
    cajaMovimientos: [
      { fecha: "15/02/2026 08:00", tipo: "Apertura", monto: 500, responsable: "Juan Pérez", estado: "Completado" },
      { fecha: "15/02/2026 12:30", tipo: "Venta", monto: 245.5, responsable: "María López", estado: "Completado" },
      { fecha: "15/02/2026 15:00", tipo: "Retiro", monto: 100, responsable: "Juan Pérez", estado: "Completado" }
    ]
  },
  {
    id: 2,
    codigo: "SUC-02-HUA",
    nombre: "NetMarket Huánuco",
    ruc: "20555666888",
    serie: "F002",
    licencia: "Vigente",
    direccion: "Jr. Lima 456, Huánuco Centro",
    horario: "07:00 AM - 11:00 PM",
    encargado: "Ana Torres",
    telefono: "912345678",
    estado: "Abierto",
    ventasHoy: 892.5,
    ventasMensual: 6540.1,
    ticketPromedio: 28.3,
    tasaMerma: 1.8,
    cajaApertura: 600,
    cajaActual: 1567.8,
    personal: [
      { nombre: "Ana Torres", cargo: "Encargada", contacto: "912345678", turno: "Mañana", estado: "Activo" },
      { nombre: "Luis García", cargo: "Cajero", contacto: "923456780", turno: "Tarde", estado: "Activo" },
      { nombre: "Carmen Díaz", cargo: "Reponedora", contacto: "934567891", turno: "Noche", estado: "Activo" }
    ],
    stock: [
      { producto: "Arroz Costeño 1kg", actual: 78, minimo: 50, estado: "Normal", reposicion: "14/02/2026" },
      { producto: "Leche Gloria 1L", actual: 45, minimo: 50, estado: "Bajo", reposicion: "12/02/2026" },
      { producto: "Aceite Primor 1L", actual: 65, minimo: 40, estado: "Normal", reposicion: "15/02/2026" },
      { producto: "Coca Cola 3L", actual: 92, minimo: 50, estado: "Normal", reposicion: "15/02/2026" }
    ],
    ventasSemanales: [720, 850, 690, 920, 1050, 1340, 892],
    productosEstrella: [
      { nombre: "Leche Gloria", ventas: 167 },
      { nombre: "Arroz Costeño", ventas: 154 },
      { nombre: "Pan Bimbo", ventas: 112 },
      { nombre: "Coca Cola 3L", ventas: 95 },
      { nombre: "Aceite Primor", ventas: 88 }
    ],
    cajaMovimientos: [
      { fecha: "15/02/2026 07:00", tipo: "Apertura", monto: 600, responsable: "Ana Torres", estado: "Completado" },
      { fecha: "15/02/2026 11:45", tipo: "Venta", monto: 312.3, responsable: "Luis García", estado: "Completado" },
      { fecha: "15/02/2026 16:20", tipo: "Venta", monto: 278.8, responsable: "Luis García", estado: "Completado" }
    ]
  },
  {
    id: 3,
    codigo: "SUC-03-CAY",
    nombre: "NetMarket Cayhuayna",
    ruc: "20555666999",
    serie: "F003",
    licencia: "Vigente",
    direccion: "Calle Los Pinos 789, Cayhuayna",
    horario: "08:00 AM - 09:00 PM",
    encargado: "Roberto Díaz",
    telefono: "955444333",
    estado: "Abierto",
    ventasHoy: 567.8,
    ventasMensual: 5660.1,
    ticketPromedio: 22.1,
    tasaMerma: 3.1,
    cajaApertura: 400,
    cajaActual: 987.3,
    personal: [
      { nombre: "Roberto Díaz", cargo: "Encargado", contacto: "955444333", turno: "Mañana", estado: "Activo" },
      { nombre: "Patricia Mendoza", cargo: "Cajera", contacto: "966555444", turno: "Tarde", estado: "Activo" }
    ],
    stock: [
      { producto: "Arroz Costeño 1kg", actual: 25, minimo: 40, estado: "Crítico", reposicion: "10/02/2026" },
      { producto: "Leche Gloria 1L", actual: 58, minimo: 50, estado: "Normal", reposicion: "14/02/2026" },
      { producto: "Aceite Primor 1L", actual: 18, minimo: 30, estado: "Crítico", reposicion: "11/02/2026" },
      { producto: "Coca Cola 3L", actual: 42, minimo: 40, estado: "Normal", reposicion: "15/02/2026" }
    ],
    ventasSemanales: [520, 610, 480, 720, 850, 980, 567],
    productosEstrella: [
      { nombre: "Arroz Costeño", ventas: 98 },
      { nombre: "Coca Cola 3L", ventas: 87 },
      { nombre: "Pan Bimbo", ventas: 76 },
      { nombre: "Leche Gloria", ventas: 65 },
      { nombre: "Galletas Oreo", ventas: 54 }
    ],
    cajaMovimientos: [
      { fecha: "15/02/2026 08:00", tipo: "Apertura", monto: 400, responsable: "Roberto Díaz", estado: "Completado" },
      { fecha: "15/02/2026 13:15", tipo: "Venta", monto: 199, responsable: "Patricia Mendoza", estado: "Completado" }
    ]
  }
])

// Estado de la vista
const selectedBranchId = ref<number | null>(null)
const showModalSucursal = ref(false)
const showModalPersonal = ref(false)
const showModalCaja = ref(false)
const editingBranch = ref<Branch | null>(null)

// Formularios reactivos
const formSucursal = reactive({
  id: null as number | null,
  codigo: '',
  nombre: '',
  ruc: '',
  serie: '',
  direccion: '',
  horaApertura: '',
  horaCierre: '',
  encargado: '',
  telefono: ''
})

const nuevoPersonal = reactive({
  nombre: '',
  cargo: '',
  contacto: '',
  turno: 'Mañana'
})

const nuevoMovimiento = reactive({
  tipo: 'Venta',
  monto: 0,
  responsable: ''
})

// Gráficos
let ventasChart: Chart | null = null
let productosChart: Chart | null = null

// Sucursal seleccionada
const sucursalSeleccionada = computed(() => {
  if (!selectedBranchId.value) return null
  return sucursalesDB.value.find(s => s.id === selectedBranchId.value) || null
})

// KPIs globales
const totalSucursales = computed(() => sucursalesDB.value.length)
const totalAlertasStock = computed(() => {
  return sucursalesDB.value.reduce((acc, s) => acc + s.stock.filter(st => st.actual <= st.minimo).length, 0)
})
const totalPersonalActivo = computed(() => {
  return sucursalesDB.value.reduce((acc, s) => acc + s.personal.length, 0)
})

// Métodos
function verDetalleSucursal(id: number) {
  selectedBranchId.value = id
}

function volverASucursales() {
  selectedBranchId.value = null
}

function prepararModalCrear() {
  editingBranch.value = null
  Object.assign(formSucursal, {
    id: null,
    codigo: `SUC-00${sucursalesDB.value.length + 1}`,
    nombre: '',
    ruc: '',
    serie: '',
    direccion: '',
    horaApertura: '',
    horaCierre: '',
    encargado: '',
    telefono: ''
  })
  showModalSucursal.value = true
}

function prepararModalEditar(branch: Branch) {
  editingBranch.value = branch
  Object.assign(formSucursal, {
    id: branch.id,
    codigo: branch.codigo,
    nombre: branch.nombre,
    ruc: branch.ruc,
    serie: branch.serie,
    direccion: branch.direccion,
    horaApertura: branch.horario.split(' - ')[0],
    horaCierre: branch.horario.split(' - ')[1],
    encargado: branch.encargado,
    telefono: branch.telefono
  })
  showModalSucursal.value = true
}

function guardarSucursal() {
  if (editingBranch.value) {
    // Editar
    const index = sucursalesDB.value.findIndex(s => s.id === editingBranch.value!.id)
    if (index !== -1) {
      sucursalesDB.value[index] = {
        ...sucursalesDB.value[index],
        codigo: formSucursal.codigo,
        nombre: formSucursal.nombre,
        ruc: formSucursal.ruc,
        serie: formSucursal.serie,
        direccion: formSucursal.direccion,
        horario: `${formSucursal.horaApertura} - ${formSucursal.horaCierre}`,
        encargado: formSucursal.encargado,
        telefono: formSucursal.telefono
      }
    }
  } else {
    // Crear nueva
    const newId = Math.max(...sucursalesDB.value.map(s => s.id)) + 1
    sucursalesDB.value.push({
      id: newId,
      codigo: formSucursal.codigo,
      nombre: formSucursal.nombre,
      ruc: formSucursal.ruc,
      serie: formSucursal.serie,
      licencia: 'Vigente',
      direccion: formSucursal.direccion,
      horario: `${formSucursal.horaApertura} - ${formSucursal.horaCierre}`,
      encargado: formSucursal.encargado,
      telefono: formSucursal.telefono,
      estado: 'Abierto',
      ventasHoy: 0,
      ventasMensual: 0,
      ticketPromedio: 0,
      tasaMerma: 0,
      cajaApertura: 0,
      cajaActual: 0,
      personal: [],
      stock: [],
      ventasSemanales: [0, 0, 0, 0, 0, 0, 0],
      productosEstrella: [],
      cajaMovimientos: []
    })
  }
  showModalSucursal.value = false
}

function abrirModalAsignarPersonal() {
  if (!sucursalSeleccionada.value) return
  nuevoPersonal.nombre = ''
  nuevoPersonal.cargo = ''
  nuevoPersonal.contacto = ''
  nuevoPersonal.turno = 'Mañana'
  showModalPersonal.value = true
}

function confirmarAsignacionPersonal() {
  const { nombre, cargo, contacto, turno } = nuevoPersonal
  if (!nombre || !cargo || !contacto) {
    alert('Por favor complete todos los campos obligatorios')
    return
  }
  const sucursal = sucursalSeleccionada.value
  if (sucursal) {
    sucursal.personal.push({
      nombre,
      cargo,
      contacto,
      turno,
      estado: 'Activo'
    })
    showModalPersonal.value = false
  }
}

function abrirModalMovimientoCaja() {
  if (!sucursalSeleccionada.value) return
  nuevoMovimiento.tipo = 'Venta'
  nuevoMovimiento.monto = 0
  nuevoMovimiento.responsable = ''
  showModalCaja.value = true
}

function confirmarMovimientoCaja() {
  const { tipo, monto, responsable } = nuevoMovimiento
  if (!monto || !responsable) {
    alert('Por favor complete todos los campos')
    return
  }
  const sucursal = sucursalSeleccionada.value
  if (sucursal) {
    sucursal.cajaMovimientos.push({
      fecha: new Date().toLocaleString('es-PE'),
      tipo,
      monto,
      responsable,
      estado: 'Completado'
    })
    if (tipo === 'Venta' || tipo === 'Apertura') {
      sucursal.cajaActual += monto
    } else {
      sucursal.cajaActual -= monto
    }
    showModalCaja.value = false
  }
}

function generarReporteReposicion() {
  alert('📄 Generando reporte de reposición de stock...')
}

function eliminarSucursal(id: number) {
  if (confirm('¿Eliminar sucursal?')) {
    sucursalesDB.value = sucursalesDB.value.filter(s => s.id !== id)
    if (selectedBranchId.value === id) volverASucursales()
  }
}

// Inicializar gráficos al cambiar de sucursal
watch(sucursalSeleccionada, (sucursal) => {
  if (!sucursal) return
  setTimeout(() => {
    const canvasVentas = document.getElementById('graficoVentasSucursal') as HTMLCanvasElement
    const canvasProductos = document.getElementById('graficoProductosEstrella') as HTMLCanvasElement

    if (ventasChart) ventasChart.destroy()
    if (productosChart) productosChart.destroy()

    if (canvasVentas) {
      ventasChart = new Chart(canvasVentas, {
        type: 'line',
        data: {
          labels: ['Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb', 'Dom'],
          datasets: [{
            label: 'Ventas (S/)',
            data: sucursal.ventasSemanales,
            borderColor: '#2b4485',
            backgroundColor: 'rgba(43,68,133,0.1)',
            tension: 0.4,
            fill: true
          }]
        },
        options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } } }
      })
    }

    if (canvasProductos) {
      productosChart = new Chart(canvasProductos, {
        type: 'bar',
        data: {
          labels: sucursal.productosEstrella.map(p => p.nombre),
          datasets: [{
            label: 'Unidades Vendidas',
            data: sucursal.productosEstrella.map(p => p.ventas),
            backgroundColor: ['#2b4485', '#3b82f6', '#10b981', '#f59e0b', '#ef4444'],
            borderRadius: 5
          }]
        },
        options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } } }
      })
    }
  }, 100)
})

const breadcrumbs = [
  { title: 'Sucursales', href: '/branches', icon: Computer },
]
</script>

<template>
  <Head title="Sucursales - NetMarket" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="p-6 space-y-6 bg-gray-100 min-h-screen text-gray-900">

      <!-- Vista de lista de sucursales -->
      <div v-if="!selectedBranchId" class="space-y-6">
        <!-- Header con botón nueva sucursal -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white p-4 rounded-xl shadow-sm">
          <div>
            <h1 class="text-2xl font-bold text-gray-800">Gestión de Sucursales</h1>
            <p class="text-sm text-gray-500">Administra las ubicaciones físicas de tu minimarket</p>
          </div>
          <button @click="prepararModalCrear" class="flex items-center gap-2 bg-[#2b4485] hover:bg-[#1a3366] text-white px-4 py-2 rounded-lg transition-colors font-medium">
            <Plus :size="18" />
            Nueva Sucursal
          </button>
        </div>

        <!-- KPIs globales -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
          <!-- Total Sucursales -->
          <div class="bg-white p-6 rounded-2xl shadow-sm hover:shadow-md transition-all min-h-[110px] flex flex-col justify-center">
            <p class="text-xs text-gray-500 uppercase tracking-wider font-medium mb-2">Total Sucursales</p>
            <h3 class="text-3xl font-bold text-gray-900">{{ totalSucursales }}</h3>
          </div>
          <!-- Ventas Totales (Mes) -->
          <div class="bg-white p-6 rounded-2xl shadow-sm hover:shadow-md transition-all min-h-[110px] flex flex-col justify-center">
            <p class="text-xs text-gray-500 uppercase tracking-wider font-medium mb-2">Ventas Totales (Mes)</p>
            <h3 class="text-3xl font-bold text-gray-900">S/ 18,320</h3>
          </div>
          <!-- Alertas de Stock -->
          <div class="bg-white p-6 rounded-2xl shadow-sm hover:shadow-md transition-all min-h-[110px] flex flex-col justify-center">
            <p class="text-xs text-gray-500 uppercase tracking-wider font-medium mb-2">Alertas de Stock</p>
            <h3 class="text-3xl font-bold text-gray-900">{{ totalAlertasStock }}</h3>
          </div>
          <!-- Personal Activo -->
          <div class="bg-white p-6 rounded-2xl shadow-sm hover:shadow-md transition-all min-h-[110px] flex flex-col justify-center">
            <p class="text-xs text-gray-500 uppercase tracking-wider font-medium mb-2">Personal Activo</p>
            <h3 class="text-3xl font-bold text-gray-900">{{ totalPersonalActivo }}</h3>
          </div>
        </div>

        <!-- Tarjetas de sucursales -->
         
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          <div v-for="branch in sucursalesDB" :key="branch.id" @click="verDetalleSucursal(branch.id)" class="bg-white rounded-2xl p-5 border-2 border-transparent hover:border-[#2b4485] hover:-translate-y-2 transition-all shadow-sm hover:shadow-xl cursor-pointer">
            <div class="flex justify-between items-start mb-3">
              <div>
                <h5 class="text-lg font-bold text-gray-800 mb-1">{{ branch.nombre }}</h5>
                <p class="text-xs text-gray-500">{{ branch.codigo }}</p>
              </div>
              <span class="px-3 py-1 rounded-full text-xs font-semibold" :class="branch.estado === 'Abierto' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'">{{ branch.estado }}</span>
            </div>
            <p class="text-sm text-gray-600 mb-3 flex items-center gap-1">
              <MapPin :size="14" class="text-gray-400" /> {{ branch.direccion }}
            </p>
            <div class="grid grid-cols-2 gap-2 mb-3">
              <div class="bg-gray-50 p-3 rounded-lg transition-all">
                <div class="text-xs text-gray-500 font-medium mb-1">Venta Hoy</div>
                <div class="text-xl font-bold text-gray-800">S/ {{ branch.ventasHoy.toFixed(2) }}</div>
              </div>
              <div class="bg-gray-50 p-3 rounded-lg transition-all">
                <div class="text-xs text-gray-500 font-medium mb-1">Personal</div>
                <div class="text-xl font-bold text-gray-800">{{ branch.personal.length }}</div>
              </div>
            </div>
            <div class="flex justify-between items-center text-xs">
              <span class="text-gray-500 flex items-center gap-1"><Clock :size="12" /> {{ branch.horario }}</span>
              <span v-if="branch.stock.filter(s => s.actual <= s.minimo).length > 0" class="px-3 py-1 rounded-full text-xs font-semibold bg-red-100 text-red-700 flex items-center gap-1 animate-pulse">
                <span class="animate-pulse">●</span> {{ branch.stock.filter(s => s.actual <= s.minimo).length }} Alertas
              </span>
              <span v-else class="px-3 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-700">OK</span>
            </div>
          </div>
        </div>
      </div>
        



      <!-- Vista detallada de sucursal -->
      <div v-else-if="sucursalSeleccionada" class="space-y-6">
        <!-- Cabecera con volver -->
        <div class="bg-white rounded-xl shadow-sm p-6">
          <div class="flex items-center gap-4">
            <button @click="volverASucursales" class="p-2 hover:bg-gray-100 rounded-lg transition">
              <ArrowLeft class="w-5 h-5 text-gray-600" />
            </button>
            <div>
              <h2 class="text-2xl font-bold text-gray-900">{{ sucursalSeleccionada.nombre }}</h2>
              <div class="flex flex-wrap items-center gap-3 mt-1 text-sm text-gray-600">
                <span class="flex items-center gap-1"><MapPin :size="14" /> {{ sucursalSeleccionada.direccion }}</span>
                <span class="flex items-center gap-1"><Phone :size="14" /> {{ sucursalSeleccionada.telefono }}</span>
                <span class="flex items-center gap-1"><Clock :size="14" /> {{ sucursalSeleccionada.horario }}</span>
                <span class="px-3 py-1 rounded-full text-xs font-semibold" :class="sucursalSeleccionada.estado === 'Abierto' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'">{{ sucursalSeleccionada.estado }}</span>
              </div>
              <p class="text-xs text-gray-400 mt-1">{{ sucursalSeleccionada.codigo }} | RUC: {{ sucursalSeleccionada.ruc }} | Licencia: {{ sucursalSeleccionada.licencia }}</p>
            </div>
          </div>
        </div>


        <!-- KPIs específicos -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
          <div class="bg-white p-6 rounded-2xl shadow-sm hover:shadow-md transition-all min-h-[110px] flex flex-col justify-center">
            <p class="text-xs text-gray-500 uppercase tracking-wider font-medium mb-2">Venta Hoy</p>
            <h3 class="text-3xl font-bold text-gray-900">S/ {{ sucursalSeleccionada.ventasHoy.toFixed(2) }}</h3>
          </div>
          <div class="bg-white p-6 rounded-2xl shadow-sm hover:shadow-md transition-all min-h-[110px] flex flex-col justify-center">
            <p class="text-xs text-gray-500 uppercase tracking-wider font-medium mb-2">Venta Mensual</p>
            <h3 class="text-3xl font-bold text-gray-900">S/ {{ sucursalSeleccionada.ventasMensual.toFixed(2) }}</h3>
          </div>
          <div class="bg-white p-6 rounded-2xl shadow-sm hover:shadow-md transition-all min-h-[110px] flex flex-col justify-center">
            <p class="text-xs text-gray-500 uppercase tracking-wider font-medium mb-2">Ticket Promedio</p>
            <h3 class="text-3xl font-bold text-gray-900">S/ {{ sucursalSeleccionada.ticketPromedio.toFixed(2) }}</h3>
          </div>
          <div class="bg-white p-6 rounded-2xl shadow-sm hover:shadow-md transition-all min-h-[110px] flex flex-col justify-center">
            <p class="text-xs text-gray-500 uppercase tracking-wider font-medium mb-2">Tasa de Merma</p>
            <h3 class="text-3xl font-bold text-gray-900">{{ sucursalSeleccionada.tasaMerma }}%</h3>
          </div>
        </div>
        

        <!-- Gráficos -->
         <!-- 
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
          <div class="bg-white rounded-2xl p-5 shadow-sm hover:shadow-md transition-all h-[420px] flex flex-col">
            <div class="mb-4 pb-2 border-b border-gray-200 flex-shrink-0">
              <h4 class="text-sm font-semibold text-gray-700 m-0">Ventas Últimos 7 Días</h4>
            </div>
            <div class="h-[320px] relative flex-1">
              <canvas id="graficoVentasSucursal"></canvas>
            </div>
          </div>
          <div class="bg-white rounded-2xl p-5 shadow-sm hover:shadow-md transition-all h-[420px] flex flex-col">
            <div class="mb-4 pb-2 border-b border-gray-200 flex-shrink-0">
              <h4 class="text-sm font-semibold text-gray-700 m-0">Productos Más Vendidos</h4>
            </div>
            <div class="h-[320px] relative flex-1">
              <canvas id="graficoProductosEstrella"></canvas>
            </div>
          </div>
        </div>
        -->


        <!-- Personal Asignado -->
         <!-- 
        <div class="bg-white rounded-xl shadow-sm">
          <div class="px-6 py-4 border-b flex justify-between items-center">
            <h3 class="font-semibold text-gray-900 flex items-center gap-2"><UserPlus :size="18" class="text-[#2b4485]" /> Personal Asignado</h3>
            <button @click="abrirModalAsignarPersonal" class="text-sm bg-[#2b4485] text-white px-3 py-1 rounded-lg hover:bg-[#1a3366]">Agregar</button>
          </div>
          <div class="p-6 overflow-x-auto">
            <table class="w-full text-sm">
              <thead class="bg-gray-50 text-gray-700">
                <tr><th class="p-3 text-left">Empleado</th><th class="p-3 text-left">Cargo</th><th class="p-3 text-left">Contacto</th><th class="p-3 text-left">Turno</th><th class="p-3 text-left">Estado</th></tr>
              </thead>
              <tbody>
                <tr v-for="(p, idx) in sucursalSeleccionada.personal" :key="idx" class="border-t">
                  <td class="p-3 flex items-center gap-2">
                    <div class="w-8 h-8 bg-[#2b4485] text-white rounded-full flex items-center justify-center text-xs font-bold">{{ p.nombre.charAt(0) }}</div>
                    {{ p.nombre }}
                  </td>
                  <td class="p-3">{{ p.cargo }}</td>
                  <td class="p-3"><a :href="`https://wa.me/51${p.contacto}`" class="text-green-600">📱 {{ p.contacto }}</a></td>
                  <td class="p-3">{{ p.turno }}</td>
                  <td class="p-3"><span class="px-2 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-700">Activo</span></td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
        -->



        <!-- Stock y Alertas -->
         <!-- 
        <div class="bg-white rounded-xl shadow-sm">
          <div class="px-6 py-4 border-b flex justify-between items-center">
            <h3 class="font-semibold text-gray-900 flex items-center gap-2"><DollarSign :size="18" class="text-[#2b4485]" /> Stock en Tiempo Real</h3>
            <div class="flex items-center gap-3">
              <span class="text-sm bg-red-100 text-red-700 px-3 py-1 rounded-full">{{ sucursalSeleccionada.stock.filter(s => s.actual <= s.minimo).length }} Alertas</span>
              <button @click="generarReporteReposicion" class="text-sm bg-yellow-500 text-white px-3 py-1 rounded-lg hover:bg-yellow-600 flex items-center gap-1"><Download :size="14" /> Reporte</button>
            </div>
          </div>
          <div class="p-6 overflow-x-auto">
            <table class="w-full text-sm">
              <thead class="bg-gray-50 text-gray-700">
                <tr><th class="p-3 text-left">Producto</th><th class="p-3 text-left">Stock Actual</th><th class="p-3 text-left">Stock Mínimo</th><th class="p-3 text-left">Estado</th><th class="p-3 text-left">Última Reposición</th><th class="p-3 text-left">Acción</th></tr>
              </thead>
              <tbody>
                <tr v-for="(s, idx) in sucursalSeleccionada.stock" :key="idx" class="border-t" :class="{'bg-red-50': s.actual <= s.minimo}">
                  <td class="p-3">{{ s.producto }}</td>
                  <td class="p-3" :class="{'font-bold text-red-600': s.actual <= s.minimo}">{{ s.actual }}</td>
                  <td class="p-3">{{ s.minimo }}</td>
                  <td class="p-3">
                    <span class="px-2 py-1 rounded-full text-xs font-semibold" :class="s.actual <= s.minimo ? 'bg-red-100 text-red-700' : 'bg-green-100 text-green-700'">{{ s.estado }}</span>
                  </td>
                  <td class="p-3">{{ s.reposicion }}</td>
                  <td class="p-3">
                    <button v-if="s.actual <= s.minimo" class="text-sm bg-orange-500 text-white px-2 py-1 rounded hover:bg-orange-600">Pedir</button>
                    <span v-else>-</span>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
        -->


      
        <!-- Caja y Finanzas -->
         <!-- -->
        <div class="bg-white rounded-xl shadow-sm">
          <div class="px-6 py-4 border-b flex justify-between items-center">
            <h3 class="font-semibold text-gray-900 flex items-center gap-2"><DollarSign :size="18" class="text-[#2b4485]" /> Caja y Finanzas</h3>
            <button @click="abrirModalMovimientoCaja" class="text-sm bg-[#2b4485] text-white px-3 py-1 rounded-lg hover:bg-[#1a3366]">Registrar Movimiento</button>
          </div>
          <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
              <div class="bg-gray-50 p-3 rounded-lg">
                <div class="text-xs text-gray-500 font-medium mb-1">Caja Apertura</div>
                <div class="text-xl font-bold text-green-600">S/ {{ sucursalSeleccionada.cajaApertura.toFixed(2) }}</div>
              </div>
              <div class="bg-gray-50 p-3 rounded-lg">
                <div class="text-xs text-gray-500 font-medium mb-1">Caja Actual</div>
                <div class="text-xl font-bold text-[#2b4485]">S/ {{ sucursalSeleccionada.cajaActual.toFixed(2) }}</div>
              </div>
              <div class="bg-gray-50 p-3 rounded-lg">
                <div class="text-xs text-gray-500 font-medium mb-1">Cierre Estimado</div>
                <div class="text-xl font-bold text-yellow-600">S/ {{ (sucursalSeleccionada.cajaActual * 1.2).toFixed(2) }}</div>
              </div>
            </div>
            <div class="overflow-x-auto">
              <table class="w-full text-sm">
                <thead class="bg-gray-50 text-gray-700">
                  <tr><th class="p-3 text-left">Fecha</th><th class="p-3 text-left">Tipo</th><th class="p-3 text-left">Monto</th><th class="p-3 text-left">Responsable</th><th class="p-3 text-left">Estado</th></tr>
                </thead>
                <tbody>
                  <tr v-for="(m, idx) in sucursalSeleccionada.cajaMovimientos" :key="idx" class="border-t">
                    <td class="p-3">{{ m.fecha }}</td>
                    <td class="p-3">
                      <span class="px-2 py-1 rounded-full text-xs font-semibold" :class="m.tipo === 'Apertura' ? 'bg-green-100 text-green-700' : m.tipo === 'Venta' ? 'bg-blue-100 text-blue-700' : 'bg-yellow-100 text-yellow-700'">{{ m.tipo }}</span>
                    </td>
                    <td class="p-3 font-bold">S/ {{ m.monto.toFixed(2) }}</td>
                    <td class="p-3">{{ m.responsable }}</td>
                    <td class="p-3"><span class="px-2 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-700">{{ m.estado }}</span></td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
      



      <!-- MODALES (sin cambios, solo clases utilitarias) -->
      <!-- Modal Nueva/Editar Sucursal -->
      
      <div v-if="showModalSucursal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4" @click.self="showModalSucursal = false">
        <div class="bg-white rounded-xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
          <div class="p-6 border-b flex justify-between items-center">
            <h3 class="text-xl font-bold text-gray-900">{{ editingBranch ? 'Editar Sucursal' : 'Nueva Sucursal' }}</h3>
            <button @click="showModalSucursal = false" class="p-2 hover:bg-gray-100 rounded-lg"><X :size="20" /></button>
          </div>
          <div class="p-6">
            <form @submit.prevent="guardarSucursal" class="space-y-4">
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">Nombre Comercial *</label>
                  <input v-model="formSucursal.nombre" type="text" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-[#2b4485] focus:border-[#2b4485]" required>
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">Código Interno *</label>
                  <input v-model="formSucursal.codigo" type="text" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-[#2b4485] focus:border-[#2b4485]" required>
                </div>
              </div>
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">RUC</label>
                  <input v-model="formSucursal.ruc" type="text" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-[#2b4485] focus:border-[#2b4485]">
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">Serie de Facturación</label>
                  <input v-model="formSucursal.serie" type="text" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-[#2b4485] focus:border-[#2b4485]">
                </div>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Dirección Completa</label>
                <input v-model="formSucursal.direccion" type="text" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-[#2b4485] focus:border-[#2b4485]" required>
              </div>
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">Horario Apertura</label>
                  <input v-model="formSucursal.horaApertura" type="time" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-[#2b4485] focus:border-[#2b4485]">
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">Horario Cierre</label>
                  <input v-model="formSucursal.horaCierre" type="time" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-[#2b4485] focus:border-[#2b4485]">
                </div>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Encargado de Tienda</label>
                <input v-model="formSucursal.encargado" type="text" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-[#2b4485] focus:border-[#2b4485]">
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Teléfono</label>
                <input v-model="formSucursal.telefono" type="tel" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-[#2b4485] focus:border-[#2b4485]">
              </div>
              <div class="flex justify-end gap-3 pt-4">
                <button type="button" @click="showModalSucursal = false" class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">Cancelar</button>
                <button type="submit" class="px-6 py-2 bg-[#2b4485] text-white rounded-lg hover:bg-[#1a3366]">Guardar Sucursal</button>
              </div>
            </form>
          </div>
        </div>
      </div>

      <!-- Modal Asignar Personal -->
       <!-- 
      <div v-if="showModalPersonal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4" @click.self="showModalPersonal = false">
        <div class="bg-white rounded-xl max-w-md w-full">
          <div class="p-6 border-b flex justify-between items-center">
            <h3 class="text-xl font-bold text-gray-900">Asignar Personal</h3>
            <button @click="showModalPersonal = false" class="p-2 hover:bg-gray-100 rounded-lg"><X :size="20" /></button>
          </div>
          <div class="p-6">
            <form @submit.prevent="confirmarAsignacionPersonal" class="space-y-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nombre *</label>
                <input v-model="nuevoPersonal.nombre" type="text" class="w-full border border-gray-300 rounded-lg px-4 py-2" required>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Cargo *</label>
                <select v-model="nuevoPersonal.cargo" class="w-full border border-gray-300 rounded-lg px-4 py-2" required>
                  <option value="">Seleccione...</option>
                  <option value="Encargado">Encargado</option>
                  <option value="Cajero">Cajero</option>
                  <option value="Reponedor">Reponedor</option>
                </select>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">WhatsApp *</label>
                <input v-model="nuevoPersonal.contacto" type="text" class="w-full border border-gray-300 rounded-lg px-4 py-2" placeholder="987654321" required>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Turno *</label>
                <select v-model="nuevoPersonal.turno" class="w-full border border-gray-300 rounded-lg px-4 py-2">
                  <option value="Mañana">Mañana</option>
                  <option value="Tarde">Tarde</option>
                  <option value="Noche">Noche</option>
                </select>
              </div>
              <div class="flex justify-end gap-3 pt-4">
                <button type="button" @click="showModalPersonal = false" class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">Cancelar</button>
                <button type="submit" class="px-6 py-2 bg-[#2b4485] text-white rounded-lg hover:bg-[#1a3366]">Agregar</button>
              </div>
            </form>
          </div>
        </div>
      </div>
      -->


      <!-- Modal Movimiento de Caja -->
        <!-- 
      <div v-if="showModalCaja" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4" @click.self="showModalCaja = false">
        <div class="bg-white rounded-xl max-w-md w-full">
          <div class="p-6 border-b flex justify-between items-center">
            <h3 class="text-xl font-bold text-gray-900">Registrar Movimiento</h3>
            <button @click="showModalCaja = false" class="p-2 hover:bg-gray-100 rounded-lg"><X :size="20" /></button>
          </div>
          <div class="p-6">
            <form @submit.prevent="confirmarMovimientoCaja" class="space-y-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Tipo *</label>
                <select v-model="nuevoMovimiento.tipo" class="w-full border border-gray-300 rounded-lg px-4 py-2">
                  <option value="Venta">Venta</option>
                  <option value="Retiro">Retiro</option>
                  <option value="Gasto">Gasto</option>
                  <option value="Apertura">Apertura</option>
                </select>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Monto (S/) *</label>
                <input v-model.number="nuevoMovimiento.monto" type="number" step="0.01" class="w-full border border-gray-300 rounded-lg px-4 py-2" required>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Responsable *</label>
                <input v-model="nuevoMovimiento.responsable" type="text" class="w-full border border-gray-300 rounded-lg px-4 py-2" required>
              </div>
              <div class="flex justify-end gap-3 pt-4">
                <button type="button" @click="showModalCaja = false" class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">Cancelar</button>
                <button type="submit" class="px-6 py-2 bg-[#2b4485] text-white rounded-lg hover:bg-[#1a3366]">Registrar</button>
              </div>
            </form>
          </div>
        </div>
      </div>
        -->

    </div>
  </AppLayout>
</template>