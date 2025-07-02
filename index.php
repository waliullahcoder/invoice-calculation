<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Create Invoice</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
</head>
<body>

<div class="container mt-5">
  <h3>Create Invoice</h3>
  <form id="invoiceForm">
    <table class="table table-bordered" id="invoiceTable">
      <thead>
        <tr>
          <th style="width: 40%">Item</th>
          <th style="width: 15%">Price (TK)</th>
          <th style="width: 10%">Qty</th>
          <th style="width: 15%">Total</th>
          <th style="width: 10%">Action</th>
        </tr>
      </thead>
      <tbody id="invoiceItems">
        <tr>
          <td>
            <select class="form-select item-select select2">
              <option value="">Select Item</option>
              @foreach($items as $item)
              <option value="{{ $item->id }}" data-price="{{ $item->sale_price }}">
                [{{ $item->id }}] {{ $item->name }}
              </option>
              @endforeach
            </select>
          </td>
          <td><input type="number" class="form-control price" readonly></td>
          <td><input type="number" class="form-control quantity" value="1"></td>
          <td><input type="number" class="form-control total" readonly></td>
          <td><button type="button" class="btn btn-danger remove-row">X</button></td>
        </tr>
      </tbody>
    </table>
    <button type="button" class="btn btn-primary mb-3" id="addRow">+ Add Item</button>

    <div class="row mb-2">
      <div class="col-md-4 offset-md-8">
        <div class="mb-2">
          <label>Subtotal</label>
          <input type="text" class="form-control" id="subtotal" readonly>
        </div>
        <div class="mb-2">
          <label>Discount (%)</label>
          <input type="number" class="form-control" id="discount" value="0">
        </div>
        <div class="mb-2">
          <label>Tax (%)</label>
          <input type="number" class="form-control" id="tax" value="0">
        </div>
        <div class="mb-2">
          <label><strong>Net Total</strong></label>
          <input type="text" class="form-control fw-bold" id="netTotal" readonly>
        </div>
      </div>
    </div>

    <div class="mb-3">
      <label>Note</label>
      <textarea class="form-control" rows="3" placeholder="Enter any note here..."></textarea>
    </div>
  </form>
</div>

<!-- Scripts -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
  function updateTotals() {
    let subtotal = 0;
    $('#invoiceItems tr').each(function () {
      const price = parseFloat($(this).find('.price').val()) || 0;
      const qty = parseInt($(this).find('.quantity').val()) || 0;
      const total = price * qty;
      $(this).find('.total').val(total.toFixed(2));
      subtotal += total;
    });

    $('#subtotal').val(subtotal.toFixed(2));
    const discount = parseFloat($('#discount').val()) || 0;
    const tax = parseFloat($('#tax').val()) || 0;

    const discounted = subtotal - (subtotal * discount / 100);
    const taxed = discounted + (discounted * tax / 100);
    $('#netTotal').val(taxed.toFixed(2));
  }

  function updateDropdowns() {
    const selectedIds = $('#invoiceItems .item-select').map(function () {
      return $(this).val();
    }).get().filter(id => id);

    $('#invoiceItems .item-select').each(function () {
      const currentVal = $(this).val();
      $(this).find('option').each(function () {
        const val = $(this).val();
        if (val && selectedIds.includes(val) && val !== currentVal) {
          $(this).prop('disabled', true);
        } else {
          $(this).prop('disabled', false);
        }
      });
    });
  }

  function initSelect2(element) {
    element.select2({ placeholder: 'Select Item', width: '100%' });
  }

  function addNewRow() {
    const rowHtml = `
      <tr>
        <td>
          <select class="form-select item-select select2">
            <option value="">Select Item</option>
            @foreach($items as $item)
            <option value="{{ $item->id }}" data-price="{{ $item->sale_price }}">
              [{{ $item->id }}] {{ $item->name }}
            </option>
            @endforeach
          </select>
        </td>
        <td><input type="number" class="form-control price" readonly></td>
        <td><input type="number" class="form-control quantity" value="1"></td>
        <td><input type="number" class="form-control total" readonly></td>
        <td><button type="button" class="btn btn-danger remove-row">X</button></td>
      </tr>
    `;
    const newRow = $(rowHtml);
    $('#invoiceItems').append(newRow);
    initSelect2(newRow.find('.item-select'));
    updateDropdowns();
  }

  $(document).ready(function () {
    initSelect2($('.item-select'));

    $('#addRow').click(function () {
      addNewRow();
    });

    $('#invoiceItems').on('change', '.item-select', function () {
      const selectedId = $(this).val();
      const selectedPrice = $(this).find(':selected').data('price') || 0;
      const currentRow = $(this).closest('tr');
      let duplicateFound = false;

      $('#invoiceItems tr').not(currentRow).each(function () {
        const rowSelect = $(this).find('.item-select');
        if (rowSelect.val() === selectedId) {
          const qtyInput = $(this).find('.quantity');
          qtyInput.val(parseInt(qtyInput.val()) + 1);
          currentRow.remove();
          duplicateFound = true;
        }
      });

      if (!duplicateFound) {
        currentRow.find('.price').val(selectedPrice);
      }

      updateTotals();
      updateDropdowns();
    });

    $('#invoiceItems').on('input', '.quantity', updateTotals);

    $('#invoiceItems').on('click', '.remove-row', function () {
      if ($('#invoiceItems tr').length > 1) {
        $(this).closest('tr').remove();
        updateTotals();
        updateDropdowns();
      } else {
        alert("At least one item is required.");
      }
    });

    $('#discount, #tax').on('input', updateTotals);
  });
</script>

</body>
</html>
