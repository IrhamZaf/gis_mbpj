document.addEventListener('DOMContentLoaded', () => {
  const table = document.getElementById('gisIncidentsTable');
  if (!table || !window.jQuery) return;
  const cat = table.getAttribute('data-category') || '';
  let url = '/api/incidents/datatable';
  if (cat) url += `?category=${encodeURIComponent(cat)}`;

  window.jQuery(table).DataTable({
    ajax: {
      url,
      dataSrc: 'data'
    },
    columns: [
      { data: 'incident_number' },
      {
        data: 'category',
        render: (d) => (d === 'sinkhole' ? 'Sinkhole' : 'Cerun')
      },
      { data: 'date_reported' },
      { data: 'risk_level' },
      { data: 'status' },
      { data: 'address', defaultContent: '' },
      {
        data: 'id',
        orderable: false,
        searchable: false,
        render: (id) =>
          `<a class="btn btn-sm btn-text-secondary" href="/incidents/${id}"><i class="icon-base ti tabler-eye"></i></a>`
      }
    ],
    order: [[2, 'desc']],
    pageLength: 25,
    responsive: true
  });
});
