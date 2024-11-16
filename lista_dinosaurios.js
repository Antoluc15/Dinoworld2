document.addEventListener('DOMContentLoaded', () => {
    const tablaDinosaurios = document.getElementById('tabla-dinosaurios');

    // Función para obtener dinosaurios desde la API
    async function cargarDinosaurios() {
        try {
            const response = await fetch('https://<tu-servidor>/api.php'); // Reemplaza con tu URL
            const dinosaurios = await response.json();

            tablaDinosaurios.innerHTML = dinosaurios.map(dino => `
                <tr>
                    <td>${dino.id}</td>
                    <td>${dino.nombre}</td>
                    <td>${dino.especie}</td>
                    <td>${dino.periodo}</td>
                    <td>${dino.descripcion || 'Sin descripción'}</td>
                    <td><img src="${dino.imagen || 'placeholder.jpg'}" alt="${dino.nombre}" width="100"></td>
                </tr>
            `).join('');
        } catch (error) {
            console.error('Error al cargar los dinosaurios:', error);
            tablaDinosaurios.innerHTML = '<tr><td colspan="6">Error al cargar los datos</td></tr>';
        }
    }

    cargarDinosaurios();
});
