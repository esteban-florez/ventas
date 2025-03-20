import Vue from 'vue'
import VueRouter from 'vue-router'
import App from '@/App.vue'
import ProductosComponent from '@/components/Inventario/ProductosComponent'
import AgregarProducto from '@/components/Inventario/AgregarProducto'
import EditarProducto from '@/components/Inventario/EditarProducto'
import MarcasCategorias from '@/components/MarcasCategorias/MarcasCategorias'
import RealizarVenta from '@/components/Ventas/RealizarVenta'
import ReporteVentas from '@/components/Ventas/ReporteVentas'
import ReporteCuentas from '@/components/Ventas/ReporteCuentas'
import ReporteApartados from '@/components/Ventas/ReporteApartados'
import ReporteCotizaciones from '@/components/Ventas/ReporteCotizaciones'
import ClientesComponent from '@/components/Clientes/ClientesComponent'
import AgregarCliente from '@/components/Clientes/AgregarCliente'
import EditarCliente from '@/components/Clientes/EditarCliente'
import UsuariosComponent from '@/components/Usuarios/UsuariosComponent'
import AgregarUsuario from '@/components/Usuarios/AgregarUsuario'
import EditarUsuario from '@/components/Usuarios/EditarUsuario'
import PerfilComponent from '@/components/Usuarios/PerfilComponent'
import CambiarPassword from '@/components/Usuarios/CambiarPassword'
import InicioComponent from '@/components/InicioComponent'
import AgregarMetodo from '@/components/Metodos/AgregarMetodo'
import MetodosComponent from '@/components/Metodos/MetodosComponent'
import EditarMetodo from '@/components/Metodos/EditarMetodo.vue'
import ChoferesComponent from '@/components/Choferes/ChoferesComponent.vue'
import EditarChofer from '@/components/Choferes/EditarChofer.vue'
import DeliveriesComponent from '@/components/Deliveries/DeliveriesComponent.vue'
import AbonosComponent from '@/components/Abonos/AbonosComponent.vue'
import RealizarAbono from '@/components/Abonos/RealizarAbono.vue'
import HistorialComponent from '@/components/Inventario/HistorialComponent.vue'
import ProveedoresComponent from '@/components/Proveedores/ProveedoresComponent.vue'
import EditarProveedor from '@/components/Proveedores/EditarProveedor.vue'
import AgregarProveedor from '@/components/Proveedores/AgregarProveedor.vue'
import PDFCotizaciones from '@/components/PDF/PDFCotizaciones.vue'
import PDFVentas from '@/components/PDF/PDFVentas.vue'
import PDFCuentas from '@/components/PDF/PDFCuentas.vue'
import PDFApartados from '@/components/PDF/PDFApartados.vue'
import PDFProductos from '@/components/PDF/PDFProductos.vue'
import PDFClientes from '@/components/PDF/PDFClientes.vue'
import PDFChoferes from '@/components/PDF/PDFChoferes.vue'
import PDFMovimientos from '@/components/PDF/PDFMovimientos.vue'
import PDFAbonos from '@/components/PDF/PDFAbonos.vue'
import PDFAbono from '@/components/PDF/PDFAbono.vue'
import PagosComponent from '@/components/Pagos/PagosComponent.vue'
import InicioSesionComponent from '@/components/Usuarios/InicioSesionComponent.vue'
import AyudanteSesion from '@/Servicios/AyudanteSesion'
import RolesComponent from '@/components/Roles/RolesComponent.vue'
import AgregarRol from '@/components/Roles/AgregarRol.vue'
import EditarRol from '@/components/Roles/EditarRol.vue'

Vue.use(VueRouter)

const routes = [
  {
    path: '/login',
    name: 'InicioSesionComponent',
    component: InicioSesionComponent,
  },
  {
    path: '/',
    name: 'InicioComponent',
    component: InicioComponent
  },
  {
    path: '/inventario',
    name: 'ProductosComponent',
    component: ProductosComponent
  },  
  {
    path: '/agregar-producto',
    name: 'AgregarProducto',
    component: AgregarProducto
  },  
  {
    path: '/editar-producto/:id',
    name: 'EditarProducto',
    component: EditarProducto
  },
  {
    path: '/marcas-y-categorias',
    name: 'MarcasCategorias',
    component: MarcasCategorias
  },
  {
    path: '/realizar-venta',
    name: 'RealizarVenta',
    component: RealizarVenta
  },  
  {
    path: '/reporte-ventas',
    name: 'ReporteVentas',
    component: ReporteVentas
  },  
  {
    path: '/reporte-cuentas',
    name: 'ReporteCuentas',
    component: ReporteCuentas
  },  
  {
    path: '/reporte-apartados',
    name: 'ReporteApartados',
    component: ReporteApartados
  },  
  {
    path: '/reporte-cotizaciones',
    name: 'ReporteCotizaciones',
    component: ReporteCotizaciones
  },
  {
    path: '/deliveries',
    name: 'DeliveriesComponent',
    component: DeliveriesComponent,
  },
  {
    path: '/choferes',
    name: 'ChoferesComponent',
    component: ChoferesComponent,
  },
  {
    path: '/editar-chofer/:id',
    name: 'EditarChofer',
    component: EditarChofer,
  },
  {
    path: '/proveedores',
    name: 'ProveedoresComponent',
    component: ProveedoresComponent,
  },
  {
    path: '/agregar-proveedor',
    name: 'AgregarProveedor',
    component: AgregarProveedor,
  },
  {
    path: '/editar-proveedor/:id',
    name: 'EditarProveedor',
    component: EditarProveedor,
  },
  {
    path: '/roles',
    name: 'RolesComponent',
    component: RolesComponent,
  },
  {
    path: '/agregar-rol',
    name: 'AgregarRol',
    component: AgregarRol,
  },
  {
    path: '/editar-rol/:id',
    name: 'EditarRol',
    component: EditarRol,
  },
  {
    path: '/pagos/:id',
    name: 'PagosComponent',
    component: PagosComponent,
  },
  {
    path: '/clientes',
    name: 'ClientesComponent',
    component: ClientesComponent
  },
  {
    path: '/agregar-cliente',
    name: 'AgregarCliente',
    component: AgregarCliente
  },
  {
    path: '/editar-cliente/:id',
    name: 'EditarCliente',
    component: EditarCliente
  },  
  {
    path: '/usuarios',
    name: 'UsuariosComponent',
    component: UsuariosComponent
  },  
  {
    path: '/agregar-usuario',
    name: 'AgregarUsuario',
    component: AgregarUsuario
  },
  {
    path: '/editar-usuario/:id',
    name: 'EditarUsuario',
    component: EditarUsuario
  },
  {
    path: '/metodos',
    name: 'MetodosComponent',
    component: MetodosComponent
  },
  {
    path: '/agregar-metodo',
    name: 'AgregarMetodo',
    component: AgregarMetodo
  },
  {
    path: '/editar-metodo/:id',
    name: 'EditarMetodo',
    component: EditarMetodo
  },
  {
    path: '/abonos/:id',
    name: 'AbonosComponent',
    component: AbonosComponent,
  },
  {
    path: '/realizar-abono/:id',
    name: 'RealizarAbono',
    component: RealizarAbono,
  },
  {
    path: '/pdf/cotizaciones',
    name: 'PDFContizaciones',
    component: PDFCotizaciones,
  },
  {
    path: '/pdf/ventas',
    name: 'PDFVentas',
    component: PDFVentas,
  },
  {
    path: '/pdf/cuentas',
    name: 'PDFCuentas',
    component: PDFCuentas,
  },
  {
    path: '/pdf/apartados',
    name: 'PDFApartados',
    component: PDFApartados,
  },
  {
    path: '/pdf/productos',
    name: 'PDFProductos',
    component: PDFProductos,
  },
  {
    path: '/pdf/clientes',
    name: 'PDFClientes',
    component: PDFClientes,
  },
  {
    path: '/pdf/choferes',
    name: 'PDFChoferes',
    component: PDFChoferes,
  },
  {
    path: '/pdf/movimientos',
    name: 'PDFMovimientos',
    component: PDFMovimientos,
  },
  {
    path: '/pdf/abonos/:id',
    name: 'PDFAbonos',
    component: PDFAbonos,
  },
  {
    path: '/pdf/comprobante-abono',
    name: 'PDFAbono',
    component: PDFAbono,
  },
  {
    path: '/historial',
    name: 'HistorialComponent',
    component: HistorialComponent,
  },
  {
    path: '/perfil',
    name: 'PerfilComponent',
    component: PerfilComponent
  },
  {
    path: '/cambiar-password',
    name: 'CambiarPassword',
    component: CambiarPassword
  },
]

const router = new VueRouter({ routes })

router.beforeEach(async (to, from, next) => {
  await App.methods.obtenerUsuario()

  const usuario = AyudanteSesion.usuario()

  if (to.name === 'InicioSesionComponent' && usuario) {
    next({ name: 'InicioComponent' })
    return
  }
  
  if (to.name !== 'InicioSesionComponent' && !usuario) {
    next({ name: 'InicioSesionComponent' })
    return
  }

  next()
})

export default router
