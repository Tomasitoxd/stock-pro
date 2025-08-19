<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Sistema de Stock — Pro</title>
  <!-- Google Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;800&display=swap" rel="stylesheet">

  <!-- External libs: jsPDF + html2canvas (CDN) -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
  <!-- NEW: Chart.js para el gráfico del balance -->
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<style>
:root{ 
  --primary:#f8a5c2; 
  --secondary:#a5d8f8; 
  --accent:#b8e994; 
  --bg:#fdfdfd; 
  --panel:#ffffff; 
  --text:#333; 
  --muted:#666; 
  --danger:#ff6b6b; 
}

*{box-sizing:border-box}
body{
  margin:0;
  font-family:'Poppins',system-ui,-apple-system,Segoe UI,Roboto;
  background:var(--bg);
  color:var(--text);
}

/* Header */
header{
  padding:18px 24px; 
  background:linear-gradient(90deg, var(--primary), var(--secondary));
  position:sticky; 
  top:0; 
  z-index:40; 
  box-shadow:0 2px 6px rgba(0,0,0,0.1);
}
.header-inner{max-width:1200px;margin:0 auto;display:flex;align-items:center;gap:16px}
.logo{width:92px;border-radius:10px;object-fit:contain}
h1{
  margin:0;
  font-weight:800;
  font-size:1.1rem;
  color:var(--text);
}
p.sub{margin:0;color:var(--muted)}

/* Container */
.container{
  max-width:1200px;
  margin:28px auto;
  padding:20px;
  border-radius:14px;
  background:var(--panel);
  box-shadow:0 6px 16px rgba(0,0,0,0.08);
}

/* Tabs */
.tabs{display:flex;gap:10px;flex-wrap:wrap;align-items:center}
.tabs button{
  background:transparent;
  border:1px solid #ddd;
  padding:10px 14px;
  border-radius:999px;
  cursor:pointer;
  font-weight:600;
  transition:all .18s;
  color:var(--text);
}
.tabs button.active{
  background:var(--secondary);
  border:1px solid var(--secondary);
  color:#fff;
}

/* Sections */
.seccion{opacity:0;transform:translateY(12px) scale(.995);max-height:0;overflow:hidden;transition:all .36s}
.seccion.activa{opacity:1;transform:none;max-height:2600px}

/* Forms */
form.grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:12px}
input,select,button{
  padding:12px;
  border-radius:10px;
  border:1px solid #ddd;
  background:#fafafa;
  color:var(--text);
  font-family:inherit
}
input:focus,select:focus{
  outline:none;
  box-shadow:0 0 8px var(--secondary);
  border-color:var(--secondary);
  transform:translateY(-2px)
}
button.primary{
  background:linear-gradient(90deg,var(--primary),var(--secondary));
  color:#fff;
  font-weight:800;
  border:none
}

/* Table */
.table-wrap{overflow:auto;border-radius:10px;border:1px solid #eee;margin-top:8px;background:white}
table{width:100%;border-collapse:collapse;min-width:760px}
th,td{padding:12px 14px;text-align:left;border-bottom:1px solid #f0f0f0}
thead th{
  position:sticky;
  top:0;
  background:var(--secondary);
  color:#fff;
  font-weight:800
}
tbody tr{transition:transform .15s, box-shadow .15s}
tbody tr:hover{transform:translateY(-4px);box-shadow:0 8px 20px rgba(0,0,0,0.08)}

/* Small helper */
.muted{color:var(--muted);font-size:.95rem}

/* Modal */
.modal-backdrop{position:fixed;inset:0;background:rgba(0,0,0,.4);display:flex;align-items:center;justify-content:center;z-index:80;opacity:0;pointer-events:none;transition:opacity .2s}
.modal-backdrop.show{opacity:1;pointer-events:auto}
.modal{
  background:var(--panel);
  padding:18px;
  border-radius:12px;
  min-width:320px;
  box-shadow:0 18px 48px rgba(0,0,0,.15);
  border:1px solid #eee
}

/* Toast */
.toast-container{position:fixed;right:18px;bottom:18px;z-index:100;display:flex;flex-direction:column;gap:8px}
.toast{padding:12px 14px;border-radius:10px;background:#fff;box-shadow:0 4px 12px rgba(0,0,0,.15);color:var(--text);font-weight:600}
.toast.success{border-left:4px solid var(--accent)}
.toast.error{border-left:4px solid var(--danger)}

/* Inputs */
.field{position:relative}
.field label{display:block;font-size:.82rem;margin-bottom:6px;color:var(--muted)}

/* PDF preview */
#pdfPreview{display:none;padding:18px;background:white;color:black;border-radius:8px}

/* Responsive */
@media (max-width:800px){.header-inner{padding:0 12px}.logo{width:78px}}

/* Anim */
@keyframes popIn{from{opacity:0;transform:translateY(8px) scale(.99)}to{opacity:1;transform:none}}
.animate-pop{animation:popIn .36s ease}

/* Cards */
.cards{display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:10px;margin:10px 0 6px}
.card{background:#fafafa;border:1px solid #eee;border-radius:12px;padding:14px}
.card h4{margin:0 0 4px;font-size:.9rem;color:var(--secondary)}
.card .big{font-size:1.4rem;font-weight:800}

.stock-bajo {
  color: #ff4444;
  font-weight: bold;
  background-color: rgba(255,0,0,0.1);
}

</style>

</head>
<body>
  <!-- LOGIN (demo) -->
  <div id="login" style="min-height:100vh;display:flex;align-items:center;justify-content:center;padding:28px;background:radial-gradient(circle at 10% 10%, rgba(207,174,109,0.04), transparent 5%), linear-gradient(135deg, rgba(0,0,0,0.9), rgba(12,12,12,0.95));">
    <form onsubmit="return iniciarSesion(event)" style="width:100%;max-width:420px;">
      <div style="background-image:url('logoazul.jpg')>
        <h2 style="margin:0 0 8px;color:var(--gold)">Bienvenido/a</h2>
        <p class="muted">Inicia sesión para administrar el stock</p>
        <div style="height:12px"></div>
        <input id="nombreEmpleadoInput" placeholder="Tu nombre" required style="width:100%;margin-bottom:10px" />
        <input id="usuario" placeholder="Usuario" required style="width:100%;margin-bottom:10px" />
        <input id="password" placeholder="Contraseña" type="password" required style="width:100%;margin-bottom:12px" />
        <div style="display:flex;gap:10px">
          <button type="submit" class="primary" style="flex:1">Entrar</button>
          <button type="button" onclick="autofillDemo()" style="flex:0">Demo</button>
        </div>
      </div>
    </form>
  </div>

  <!-- APP -->
  <div id="app" style="display:none">
    <header>
      <div class="header-inner">
        <img src="logoazul.jpg" alt="logo" class="logo"/>
        <div>
          <h1>🌸 Sistema de Control de Stock — Pro</h1>
          <p id="bienvenida" class="sub">&nbsp;</p>
        </div>
      </div>
    </header>

    <div class="container">
      <div class="tabs" role="tablist" aria-label="Pestañas principales">
        <button id="tab-agregar" onclick="mostrarSeccion('agregar')">➕ Agregar / Editar</button>
        <button id="tab-ver" onclick="mostrarSeccion('ver')">📦 Ver Stock</button>
        <button id="tab-pagos" onclick="mostrarSeccion('pagos')">💰 Pagos</button>
        <button id="tab-cierres" onclick="mostrarSeccion('cierres')">🧾 Cierres</button>
        <button onclick="cerrarSesion()">🔒 Cerrar Sesión</button>
      </div>

      <!-- Agregar -->
      <section id="seccion-agregar" class="seccion activa animate-pop">
        <h2 id="titulo-form">➕ Agregar Producto</h2>
        <form id="form-agregar" class="grid" data-mode="add">
          <input type="hidden" id="productoId" name="id" />
          <div class="field"><label>Nombre</label><input name="nombre" required /></div>
          <div class="field"><label>Cantidad</label><input name="cantidad" type="number" required /></div>
          <div class="field"><label>Precio</label><input name="descripcion" placeholder="Ej: 1200" /></div>
          <div class="field"><label>Código de barras</label><input name="codigo_barras" required /></div>
          <div class="field"><label>Foto</label><input type="file" name="foto" accept="image/*" /></div>
          <div style="grid-column:span 2;display:flex;gap:8px">
            <button id="btnForm" type="submit" class="primary">Agregar al stock</button>
            <button type="button" onclick="limpiarFormulario()">Limpiar</button>
          </div>
        </form>
      </section>

      <!-- Ver -->
      <section id="seccion-ver" class="seccion">
        <h2>📋 Ver Stock</h2>
        <div style="display:flex;gap:12px;align-items:center;margin-bottom:8px">
          <div style="flex:1;max-width:420px"><input id="buscar" placeholder="Buscar por nombre o código..." style="width:100%" /></div>
          <div class="muted">Items: <span id="contador-productos">0</span></div>
        </div>
        <div class="table-wrap">
          <table aria-describedby="tabla-productos">
            <thead>
              <tr><th>ID</th><th>Nombre</th><th>Cód.</th><th>Cant.</th><th>Precio</th><th>Foto</th><th>Acción</th></tr>
            </thead>
            <tbody id="tabla-productos"></tbody>
          </table>
        </div>
      </section>

      <!-- Pagos -->
    <section id="seccion-pagos" class="seccion">
  <h2>💰 Registro de Pagos</h2>
  <form id="form-pagos" class="grid">
    <div class="field"><label>Cliente</label><input name="nombre_cliente" required /></div>
    <div class="field"><label>Concepto</label><input name="concepto" required /></div>
    <div class="field"><label>Monto</label><input name="monto" type="number" step="0.01" required /></div>
    <div class="field"><label>Fecha</label><input name="fecha" type="date" required /></div>
    <div class="field"><label>Método</label>
      <select name="metodo_pago" required>
        <option value="">Seleccionar</option>
        <option>Efectivo</option>
        <option>Transferencia</option>
        <option>Tarjeta</option>
      </select>
    </div>
    <div style="grid-column:span 2;display:flex;gap:8px">
      <button type="submit" class="primary">Registrar pago</button>
      <button type="button" onclick="abrirCerrarCajaModal()">🧾 Cerrar Caja</button>
      
    </div>
  </form>

 

  <div class="cards">
    <div class="card">
      <h4>Ingresos del día</h4>
      <div class="big" id="ingresos-dia">$0.00</div>
    </div>
    <div class="card">
      <h4>Gastos del día</h4>
      <div class="big" id="gastos-dia">$0.00</div>
    </div>
    <div class="card">
      <h4>Neto</h4>
      <div class="big" id="neto-dia">$0.00</div>
    </div>
  </div>

  <div style="background:rgba(255,255,255,0.02);border:1px solid rgba(255,255,255,0.04);padding:14px;border-radius:12px;margin:8px 0 14px">
    <label for="filtroFecha">Filtrar por fecha:</label>
    <input id="filtroFecha" type="date" />
    <div style="height:10px"></div>
    <canvas id="chartBalanceDia" height="110" aria-label="Gráfico de balance del día" role="img"></canvas>
  </div>

  <h3 style="margin-top:6px">📄 Pagos Registrados</h3>
  <div class="table-wrap" style="margin-top:8px">
    <table>
      <thead>
        <tr><th>ID</th><th>Cliente</th><th>Concepto</th><th>Monto</th><th>Fecha</th><th>Hora</th><th>Acción</th></tr>
      </thead>
      <tbody id="tabla-pagos"></tbody>
    </table>
  </div>

  <h2 style="margin-top:18px">📉 Registrar Gasto</h2>
  <form id="form-gastos" class="grid">
    <div class="field"><label>Proveedor / Concepto</label><input name="concepto" required /></div>
    <div class="field"><label>Monto</label><input name="monto" type="number" step="0.01" required /></div>
    <div class="field"><label>Fecha</label><input name="fecha" type="date" required /></div>
    <div style="grid-column:span 2;display:flex;gap:8px">
      <button type="submit" class="primary">Registrar gasto</button>
    </div>
  </form>
</section>

        <h3 style="margin-top:6px">🧾 Gastos Registrados</h3>
        <div class="table-wrap" style="margin-top:8px">
          <table>
            <thead><tr><th>ID</th><th>Concepto</th><th>Monto</th><th>Fecha</th><th>Hora</th><th>Acción</th></tr></thead>
            <tbody id="tabla-gastos"></tbody>
          </table>
        </div>
      </section>

      <!-- Cierres -->
      <section id="seccion-cierres" class="seccion">
        <h2>🧾 Historial de Cierres</h2>
   

<form action="export_balance.php" method="get" target="_blank">
  <label>Fecha:</label>
  <input type="date" name="fecha" required>
  <button class="primary" type="submit">Descargar Balance (día)</button>
</form>



        <div class="table-wrap">
          <table>
            <thead><tr><th>ID</th><th>Fecha</th><th>Hora</th><th>Total ($)</th></tr></thead>
            <tbody id="tabla-cierres"></tbody>
          </table>
        </div>

        <!-- Hidden printable preview used to build the PDF via html2canvas + jsPDF -->
        <div id="pdfPreview" aria-hidden="true"></div>
      </section>

    </div>
  </div>

  <!-- Modal for deleting payments / closing caja -->
  <div id="modal" class="modal-backdrop" role="dialog" aria-modal="true" style="display:none">
    <div class="modal" id="modalContent">
      <h3 id="modalTitle">Acción</h3>
      <p id="modalMessage" class="muted">&nbsp;</p>
      <div id="modalBody"></div>
      <div style="display:flex;gap:8px;margin-top:12px;justify-content:flex-end">
        <button onclick="cerrarModal()">Cancelar</button>
        <button id="modalConfirm" class="primary">Confirmar</button>
      </div>
    </div>
  </div>

  <!-- Toasts -->
  <div class="toast-container" id="toasts"></div>
<script> 
  // ------------------------
// DEMO USERS / LOGIN
// ------------------------
const usuariosValidos = [{ usuario: 'admin', password: 'admin' }];
let nombreEmpleado = '';

function autofillDemo(){
  document.getElementById('usuario').value='admin';
  document.getElementById('password').value='admin';
  document.getElementById('nombreEmpleadoInput').value='Empleado Demo';
}

function iniciarSesion(e){
  e.preventDefault();
  const usuario=document.getElementById('usuario').value.trim();
  const password=document.getElementById('password').value.trim();
  const nombre=document.getElementById('nombreEmpleadoInput').value.trim();
  const valido = usuariosValidos.find(u=>u.usuario===usuario&&u.password===password);
  if(!valido){ mostrarToast('Usuario o contraseña incorrectos','error'); return false; }
  nombreEmpleado = nombre || usuario;
  document.getElementById('login').style.display='none';
  document.getElementById('app').style.display='block';
  document.getElementById('bienvenida').textContent = `Bienvenido/a, ${nombreEmpleado}`;
  // default tab
  document.querySelectorAll('.tabs button').forEach(b=>b.classList.remove('active'));
  document.getElementById('tab-ver').classList.add('active');
  mostrarSeccion('ver');
  cargarPagos(); cargarGastos(); cargarProductos(); cargarCierres(); actualizarBalanceYGrafico();
  return false;
}

function cerrarSesion(){
  if(confirm('Cerrar sesión?')){
    nombreEmpleado='';
    document.getElementById('app').style.display='none';
    document.getElementById('login').style.display='flex';
  }
}

function mostrarSeccion(nombre){
  document.querySelectorAll('.seccion').forEach(s=>s.classList.remove('activa'));
  const el = document.getElementById('seccion-'+nombre); if(el) el.classList.add('activa');
  document.querySelectorAll('.tabs button').forEach(b=>b.classList.remove('active'));
  const map={agregar:'tab-agregar', ver:'tab-ver', pagos:'tab-pagos', cierres:'tab-cierres'};
  if(map[nombre]) document.getElementById(map[nombre]).classList.add('active');
  if(nombre==='pagos'){ cargarPagos(); cargarGastos(); actualizarBalanceYGrafico(); }
  if(nombre==='ver') cargarProductos();
  if(nombre==='cierres') cargarCierres();
}

// ------------------------
// TOASTS
// ------------------------
function mostrarToast(msg, tipo='success', duration=3500){
  const t=document.createElement('div');
  t.className='toast '+(tipo==='error'?'error':'success');
  t.textContent=msg;
  document.getElementById('toasts').appendChild(t);
  setTimeout(()=>{ t.style.opacity='0'; setTimeout(()=>t.remove(),400) }, duration);
}

// ------------------------
// MODAL
// ------------------------
function abrirModal({title='Confirmar', message='', bodyHtml='', confirmText='Confirmar', onConfirm=null}){
  const modal = document.getElementById('modal');
  modal.style.display='flex';
  setTimeout(()=>modal.classList.add('show'),10);
  document.getElementById('modalTitle').textContent = title;
  document.getElementById('modalMessage').textContent = message;
  document.getElementById('modalBody').innerHTML = bodyHtml;
  const btn = document.getElementById('modalConfirm');
  btn.textContent = confirmText;
  btn.onclick = async ()=>{ if(onConfirm) await onConfirm(); cerrarModal(); }
}
function cerrarModal(){ const modal=document.getElementById('modal'); modal.classList.remove('show'); setTimeout(()=>modal.style.display='none',220); }

// ------------------------
// FORM AGREGAR / EDITAR PRODUCTOS
// ------------------------
const formAgregar = document.getElementById('form-agregar');
const btnForm = document.getElementById('btnForm');
if(formAgregar){
  formAgregar.addEventListener('submit', async e=>{
    e.preventDefault();
    const modo = formAgregar.dataset.mode || 'add';
    const datos = new FormData(formAgregar);
    const url = modo==='add' ? 'add_stock.php' : 'update_stock.php';
    try{
      const resp = await fetch(url, { method:'POST', body:datos });
      const info = await resp.json();
      if(info.success){
        mostrarToast(modo==='add' ? 'Producto agregado' : 'Producto actualizado');
        formAgregar.reset();
        formAgregar.dataset.mode='add';
        document.getElementById('titulo-form').textContent='➕ Agregar Producto';
        btnForm.textContent='Agregar al stock';
        cargarProductos();
        mostrarSeccion('ver');
      } else {
        mostrarToast(info.error||'Error en operación','error');
      }
    }catch(err){ console.error(err); mostrarToast('Ocurrió un error al comunicarse con el servidor','error'); }
  });
}

function limpiarFormulario(){
  if(!formAgregar) return;
  formAgregar.reset();
  formAgregar.dataset.mode='add';
  document.getElementById('titulo-form').textContent='➕ Agregar Producto';
  btnForm.textContent='Agregar al stock';
}

// ------------------------
// PRODUCTOS
// ------------------------
async function cargarProductos(){
  try{
    const res = await fetch('fetch_stock.php');
    if(!res.ok) throw new Error('Error server');
    const datos = await res.json();
    const tabla = document.getElementById('tabla-productos');
    if(!Array.isArray(datos)) throw new Error('Formato inválido');
    tabla.innerHTML = datos.map(p=>{
  // chequear si el stock es bajo (ej: 5)
  const esBajo = p.cantidad <= 5;

  if(esBajo){
    // opcional: mostrar alerta toast cada vez que cargue
    mostrarToast(`⚠️ ${p.nombre} tiene poco stock (${p.cantidad})`, "error");
  }

  return `
    <tr class="animate-pop ${esBajo ? 'stock-bajo' : ''}">
      <td>${p.id}</td>
      <td>${p.nombre}</td>
      <td>${p.codigo_barras}</td>
      <td class="${esBajo ? 'stock-bajo' : ''}">${p.cantidad}</td>
      <td>${p.descripcion||''}</td>
      <td>${p.foto?`<img src="${p.foto}" alt="Foto" style="width:64px;border-radius:6px">`:''}</td>
      <td style="display:flex;gap:6px"><button class="editar" onclick="editarProducto(${p.id})">✏️</button></td>
    </tr>
  `;
}).join('');

    const contador = document.getElementById('contador-productos');
    if(contador) contador.textContent = datos.length;
  }catch(err){
    console.error(err);
    const tabla = document.getElementById('tabla-productos');
    if(tabla) tabla.innerHTML=`<tr><td colspan="7" style="text-align:center;color:rgba(255,255,255,.6)">No se pudieron cargar los productos.</td></tr>`;
  }
}

async function editarProducto(id){
  try{
    const res = await fetch(`fetch_stock.php?id=${id}`);
    if(!res.ok) throw new Error('No se pudo obtener el producto');
    const prod = await res.json();
    if(!prod.id) throw new Error('Producto no encontrado');
    formAgregar.dataset.mode='edit';
    document.getElementById('productoId').value = prod.id;
    formAgregar.nombre.value = prod.nombre;
    formAgregar.cantidad.value = prod.cantidad;
    formAgregar.descripcion.value = prod.descripcion;
    formAgregar.codigo_barras.value = prod.codigo_barras;
    btnForm.textContent='Actualizar';
    document.getElementById('titulo-form').textContent='✏️ Editar Producto';
    mostrarSeccion('agregar');
  }catch(err){ mostrarToast(err.message,'error'); }
}

// Buscador (cliente-side)
const buscarInput = document.getElementById('buscar');
if(buscarInput){
  buscarInput.addEventListener('input', function(){
    const filtro=this.value.toLowerCase();
    const filas=document.querySelectorAll('#tabla-productos tr');
    filas.forEach(fila=>{
      const nombre=fila.children[1]?.textContent.toLowerCase()||'';
      const codigo=fila.children[2]?.textContent.toLowerCase()||'';
      fila.style.display=(nombre.includes(filtro)||codigo.includes(filtro))? '':'none';
    });
  });
}

// ------------------------
// VARIABLES
// ------------------------
// ------------------------
// VARIABLES
// ------------------------
let chartBalance = null;
let _cachePagos = [];
let _cacheGastos = [];

function setCachedPagos(p){ _cachePagos = Array.isArray(p) ? p : []; }
function setCachedGastos(g){ _cacheGastos = Array.isArray(g) ? g : []; }
function sumatoria(arr){ return arr.reduce((acc,x)=> acc + (parseFloat(x.monto)||0), 0); }
// ------------------------
// PAGOS
// ------------------------
async function cargarPagos(){
    const fecha = document.getElementById('filtroFecha').value || '';
    let url = 'fetch_pagos.php';
    if(fecha) url += `?fecha_inicio=${fecha}&fecha_fin=${fecha}`;
    try{
        const res = await fetch(url, { credentials: 'include' }); // 🔹 Cookies de sesión
        if(!res.ok) throw new Error('Error en respuesta');
        const pagos = await res.json();
        const tbody = document.getElementById('tabla-pagos');
        if(!Array.isArray(pagos) || pagos.length===0){
            tbody.innerHTML = `<tr><td colspan="7" style="text-align:center">No hay pagos para esta fecha.</td></tr>`;
            setCachedPagos([]);
            return;
        }
        tbody.innerHTML = pagos.map(p=>`
            <tr>
                <td>${p.id}</td>
                <td>${p.nombre_cliente}</td>
                <td>${p.concepto}</td>
                <td>$${parseFloat(p.monto).toFixed(2)}</td>
                <td>${p.fecha}</td>
                <td>${p.hora}</td>
                <td style="display:flex;gap:6px">
                    <button class="eliminar" onclick="confirmarEliminarPago(${p.id}, '${p.monto}')">🗑️</button>
                </td>
            </tr>
        `).join('');
        setCachedPagos(pagos);
    }catch(err){
        console.error(err);
        document.getElementById('tabla-pagos').innerHTML = `<tr><td colspan="7" style="text-align:center;color:red">Error cargando pagos.</td></tr>`;
        setCachedPagos([]);
    }
}

// ------------------------
// GASTOS
// ------------------------
async function cargarGastos(){
    const fecha = document.getElementById('filtroFecha').value || '';
    let url = 'fetch_gastos.php';
    if(fecha) url += `?fecha_inicio=${fecha}&fecha_fin=${fecha}`;
    try{
        const res = await fetch(url, { credentials: 'include' }); // 🔹 Cookies de sesión
        if(!res.ok) throw new Error('Error en respuesta');
        const gastos = await res.json();
        const tbody = document.getElementById('tabla-gastos');
        if(!Array.isArray(gastos) || gastos.length===0){
            tbody.innerHTML = `<tr><td colspan="6" style="text-align:center">No hay gastos para esta fecha.</td></tr>`;
            setCachedGastos([]);
            return;
        }
        tbody.innerHTML = gastos.map(g=>`
            <tr>
                <td>${g.id}</td>
                <td>${g.concepto}</td>
                <td>$${parseFloat(g.monto).toFixed(2)}</td>
                <td>${g.fecha}</td>
                <td>${g.hora}</td>
                <td style="display:flex;gap:6px">
                    <button class="eliminar" onclick="confirmarEliminarGasto(${g.id}, '${g.monto}')">🗑️</button>
                </td>
            </tr>
        `).join('');
        setCachedGastos(gastos);
    }catch(err){
        console.error(err);
        document.getElementById('tabla-gastos').innerHTML = `<tr><td colspan="6" style="text-align:center;color:red">Error cargando gastos.</td></tr>`;
        setCachedGastos([]);
    }
}




// ---- Tu código actual para cargar pagos y gastos ----
// ... (todo lo que me pasaste arriba)
// ---------------------
// REGISTRAR PAGO
// ---------------------
document.getElementById('form-pagos').addEventListener('submit', async function(e) {
    e.preventDefault();

    const formData = new FormData(this);

    try {
        const res = await fetch('add_pago.php', {
            method: 'POST',
            body: formData,
            credentials: 'include' // 🔹 Esto envía cookies de sesión
        });

        const data = await res.json();

        if (data.success) {
            alert('✅ Pago registrado con éxito');
            this.reset();
            cargarPagos(); // recargar lista
        } else {
            alert('❌ Error: ' + data.error);
            console.error('DEBUG', data.debug || '');
        }
    } catch (err) {
        alert('⚠ Error de conexión: ' + err.message);
    }
});


// ---------------------
// REGISTRAR GASTO
// ---------------------
document.getElementById('form-gastos').addEventListener('submit', async function(e) {
    e.preventDefault();

    const formData = new FormData(this);

    try {
        const res = await fetch('add_gasto.php', {
            method: 'POST',
            body: formData,
            credentials: 'include' // 🔹 Mantiene la sesión
        });

        const data = await res.json();

        if (data.success) {
            alert('✅ Gasto registrado con éxito');
            this.reset();
            cargarGastos(); // recargar lista
        } else {
            alert('❌ Error: ' + data.error);
            console.error('DEBUG', data.debug || '');
        }
    } catch (err) {
        alert('⚠ Error de conexión: ' + err.message);
    }
});


// ------------------------
// BALANCE + GRÁFICO
// ------------------------
function actualizarBalanceYGrafico(){
    const ingresos = sumatoria(_cachePagos);
    const gastos   = sumatoria(_cacheGastos);
    const neto     = ingresos - gastos;

    document.getElementById('ingresos-dia').textContent = `$${ingresos.toFixed(2)}`;
    document.getElementById('gastos-dia').textContent   = `$${gastos.toFixed(2)}`;
    document.getElementById('neto-dia').textContent     = `$${neto.toFixed(2)}`;

    const ctx = document.getElementById('chartBalanceDia').getContext('2d');
    const data = { 
        labels:['Ingresos','Gastos','Neto'], 
        datasets:[{ label:'$ del día', data:[ingresos, gastos, neto], backgroundColor:['#4caf50','#f44336','#2196f3'] }]
    };
    const options = { responsive:true, plugins:{ legend:{ display:false }, tooltip:{ callbacks:{ label:(ctx)=> `$ ${ctx.parsed.y?.toFixed(2)}` } } }, scales:{ y:{ beginAtZero:true } } };
    
    if(chartBalance){ 
        chartBalance.data = data; 
        chartBalance.update(); 
    } else { 
        chartBalance = new Chart(ctx, { type:'bar', data, options }); 
    }
}

// ------------------------
// FUNCIÓN GLOBAL PARA CARGAR Y ACTUALIZAR
// ------------------------
async function cargarDatosYActualizar(){
    await cargarPagos();
    await cargarGastos();
    actualizarBalanceYGrafico();
}

// ------------------------
// FILTRO POR FECHA
// ------------------------
document.getElementById('filtroFecha').addEventListener('change', async ()=>{
    await cargarDatosYActualizar();
});

// ------------------------
// INICIALIZACIÓN
// ------------------------
document.addEventListener('DOMContentLoaded', async ()=>{
    const hoy = new Date().toISOString().split('T')[0];
    document.getElementById('filtroFecha').value = hoy;
    await cargarDatosYActualizar();
});

// ------------------------
// ELIMINAR PAGO / GASTO (con password)
// ------------------------
function confirmarEliminarPago(id, monto){
  abrirModal({
    title:'Eliminar pago',
    message:`Vas a eliminar un pago por $${parseFloat(monto).toFixed(2)}. Ingresá la contraseña para confirmar.`,
    bodyHtml:`<input id="pwdEliminar" type="password" placeholder="Contraseña" style="width:100%;padding:10px;border-radius:8px;border:1px solid rgba(0,0,0,.08)" />`,
    confirmText:'Eliminar',
    onConfirm: async ()=>{
      const pwd = document.getElementById('pwdEliminar').value; if(!pwd){ mostrarToast('Se requiere contraseña','error'); return; }
      try{
        const res = await fetch('delete_pago.php', { method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded'}, body:`id=${id}&password=${encodeURIComponent(pwd)}` });
        const data = await res.json();
        if(data.success){ mostrarToast('Pago eliminado'); await cargarPagos(); actualizarBalanceYGrafico(); }
        else { mostrarToast(data.error||'No se pudo eliminar el pago','error'); }
      }catch(err){ console.error(err); mostrarToast('Error al eliminar pago','error'); }
    }
  });
}

function confirmarEliminarGasto(id, monto){
  abrirModal({
    title:'Eliminar gasto',
    message:`Vas a eliminar un gasto por $${parseFloat(monto).toFixed(2)}. Ingresá la contraseña para confirmar.`,
    bodyHtml:`<input id="pwdEliminar" type="password" placeholder="Contraseña" style="width:100%;padding:10px;border-radius:8px;border:1px solid rgba(0,0,0,.08)" />`,
    confirmText:'Eliminar',
    onConfirm: async ()=>{
      const pwd = document.getElementById('pwdEliminar').value;
      if(!pwd){ 
        mostrarToast('Se requiere contraseña','error'); 
        return; 
      }
      try{
        const res = await fetch('delete_gasto.php', { 
          method:'POST', 
          headers:{'Content-Type':'application/x-www-form-urlencoded'}, 
          body:`id=${id}&password=${encodeURIComponent(pwd)}`,
          credentials: 'include' // 🔹 ahora sí viaja la cookie de sesión
        });
        const data = await res.json();
        if(data.success){ 
          mostrarToast('Gasto eliminado'); 
          await cargarGastos(); 
          actualizarBalanceYGrafico(); 
        } else { 
          mostrarToast(data.error||'No se pudo eliminar el gasto','error'); 
        }
      }catch(err){ 
        console.error(err); 
        mostrarToast('Error al eliminar gasto','error'); 
      }
    }
  });
}

// ------------------------
// CIERRES + PDF
// ------------------------
async function cargarCierres(){
  try{
    const res = await fetch('fetch_cierres.php'); if(!res.ok) throw new Error('Error server');
    const cierres = await res.json();
    const tbody = document.getElementById('tabla-cierres');
    if(!Array.isArray(cierres) || cierres.length===0){ if(tbody) tbody.innerHTML = `<tr><td colspan="4" style="text-align:center">No hay cierres.</td></tr>`; return; }
    if(tbody) tbody.innerHTML = cierres.map(c=>`<tr><td>${c.id}</td><td>${c.fecha}</td><td>${c.hora}</td><td>$${parseFloat(c.total).toFixed(2)}</td></tr>`).join('');
  }catch(err){ console.error(err); const tbody=document.getElementById('tabla-cierres'); if(tbody) tbody.innerHTML = `<tr><td colspan="4" style="text-align:center;color:red">Error cargando cierres.</td></tr>`; }
}

function abrirCerrarCajaModal(){
  abrirModal({
    title:'Cerrar caja',
    message:'¿Deseás cerrar la caja ahora? Esta acción generará un cierre con el total del día.',
    bodyHtml:`<div class="muted">Usuario: ${nombreEmpleado}</div>`,
    confirmText:'Cerrar caja',
    onConfirm: async ()=>{
      try{
        const res = await fetch('cerrar_caja.php', { method:'POST' });
        const data = await res.json();
        if(data.success){ mostrarToast(`Caja cerrada por $${parseFloat(data.total).toFixed(2)}`); cargarCierres(); }
        else { mostrarToast(data.error||'Error al cerrar caja','error'); }
      }catch(err){ console.error(err); mostrarToast('Error al cerrar caja','error'); }
    }
  });
}

async function exportarCierrePDF(){
  const fecha = document.getElementById('filtroCierre').value;
  if(!fecha){ mostrarToast('Seleccioná una fecha para exportar','error'); return; }
  try{
    const res = await fetch(`fetch_cierres.php?fecha=${fecha}`);
    if(!res.ok) throw new Error('Error al solicitar cierre');
    const cierres = await res.json();
    if(!Array.isArray(cierres) || cierres.length===0){ mostrarToast('No se encontró cierre para esa fecha','error'); return; }

    const cierre = cierres[0];
    const pagosRes = await fetch(`fetch_pagos.php?fecha=${fecha}`);
    const pagos = await pagosRes.json();
    const gastosRes = await fetch(`fetch_gastos.php?fecha=${fecha}`);
    const gastos = await gastosRes.json();

    const preview = document.getElementById('pdfPreview');
    preview.style.display='block';
    preview.innerHTML = `
      <div style="padding:18px;font-family:Arial;line-height:1.4">
        <h2 style="margin:0 0 6px">Balance de Cierre — ${cierre.fecha}</h2>
        <div style="margin-bottom:10px;">Generado por: ${nombreEmpleado} — Hora: ${cierre.hora}</div>
        <h3>Ingresos</h3>
        <table style="width:100%;border-collapse:collapse;margin-bottom:8px">
          <thead>
            <tr style="background:#f2f2f2;color:#000"><th style="padding:8px;border:1px solid #ddd">ID</th><th style="padding:8px;border:1px solid #ddd">Cliente</th><th style="padding:8px;border:1px solid #ddd">Concepto</th><th style="padding:8px;border:1px solid #ddd">Monto</th></tr>
          </thead>
          <tbody>
            ${Array.isArray(pagos) ? pagos.map(p=>`<tr><td style="padding:8px;border:1px solid #ddd">${p.id}</td><td style="padding:8px;border:1px solid #ddd">${p.nombre_cliente}</td><td style="padding:8px;border:1px solid #ddd">${p.concepto}</td><td style="padding:8px;border:1px solid #ddd">$${parseFloat(p.monto).toFixed(2)}</td></tr>`).join('') : ''}
          </tbody>
        </table>
        <h3>Gastos</h3>
        <table style="width:100%;border-collapse:collapse">
          <thead>
            <tr style="background:#f2f2f2;color:#000"><th style="padding:8px;border:1px solid #ddd">ID</th><th style="padding:8px;border:1px solid #ddd">Concepto</th><th style="padding:8px;border:1px solid #ddd">Monto</th></tr>
          </thead>
          <tbody>
            ${Array.isArray(gastos) ? gastos.map(g=>`<tr><td style=\"padding:8px;border:1px solid #ddd\">${g.id}</td><td style=\"padding:8px;border:1px solid #ddd\">${g.concepto}</td><td style=\"padding:8px;border:1px solid #ddd\">$${parseFloat(g.monto).toFixed(2)}</td></tr>`).join('') : ''}
          </tbody>
        </table>
        <h3 style="margin-top:12px">Total ingresos: $${sumatoria(pagos||[]).toFixed(2)} — Total gastos: $${sumatoria(gastos||[]).toFixed(2)} — Neto: $${(sumatoria(pagos||[])-sumatoria(gastos||[])).toFixed(2)}</h3>
      </div>
    `;

    await new Promise(r=>setTimeout(r,120));
    const canvas = await html2canvas(preview, { scale:2 });
    const imgData = canvas.toDataURL('image/png');
    const { jsPDF } = window.jspdf;
    const pdf = new jsPDF('p','mm','a4');
    const pageWidth = pdf.internal.pageSize.getWidth();
    const imgProps = pdf.getImageProperties(imgData);
    const pdfHeight = (imgProps.height * pageWidth) / imgProps.width;
    pdf.addImage(imgData, 'PNG', 8, 8, pageWidth-16, pdfHeight);
    pdf.save(`balance_cierre_${fecha}.pdf`);

    preview.style.display='none';
  }catch(err){ console.error(err); mostrarToast('Error al generar PDF','error'); }
}

// ------------------------
// INIT DEFAULTS
// ------------------------
document.addEventListener('DOMContentLoaded', async ()=>{
  const hoy = new Date().toISOString().split('T')[0];
  const f1=document.getElementById('filtroFecha'); if(f1) f1.value = hoy;
  const f2=document.getElementById('filtroCierre'); if(f2) f2.value = hoy;
  document.addEventListener('keydown', e=>{ if(e.key==='Escape') cerrarModal(); });
  // Carga inicial para la fecha actual
  await cargarPagos();
  await cargarGastos();
  actualizarBalanceYGrafico();
});



   </script>



</body>
</html>