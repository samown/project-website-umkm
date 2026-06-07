// ─── Global helper dipanggil tombol "Tambah Keranjang" ───
function addToCart(id, name, price) {
  // Dispatch event ke Alpine component
  window.dispatchEvent(new CustomEvent('add-to-cart', { detail: { id, name, price } }));
  // Tampilkan keranjang
  window.dispatchEvent(new CustomEvent('open-cart'));
}

// ─── Alpine.js Cart Component ───
function cart() {
  return {
    open: false,
    showCheckout: false,
    items: [],
    loading: false,
    checkoutError: '',
    form: { name: '', phone: '', address: '', notes: '' },

    loadCart() {
      // Dengarkan event tambah produk dari tombol di luar komponen
      window.addEventListener('add-to-cart', (e) => this.add(e.detail));
      window.addEventListener('open-cart',   ()  => { this.open = true; });
    },

    add({ id, name, price }) {
      const existing = this.items.find(i => i.id === id);
      if (existing) {
        existing.qty++;
      } else {
        this.items.push({ id, name, price, qty: 1 });
      }
    },

    inc(id) {
      const item = this.items.find(i => i.id === id);
      if (item) item.qty++;
    },

    dec(id) {
      const item = this.items.find(i => i.id === id);
      if (!item) return;
      if (item.qty > 1) {
        item.qty--;
      } else {
        this.items = this.items.filter(i => i.id !== id);
      }
    },

    totalQty()   { return this.items.reduce((s, i) => s + i.qty, 0); },
    totalPrice() { return this.items.reduce((s, i) => s + i.price * i.qty, 0); },

    async doCheckout() {
      this.checkoutError = '';
      if (!this.form.name || !this.form.phone || !this.form.address) {
        this.checkoutError = 'Nama, nomor HP, dan alamat wajib diisi.';
        return;
      }
      this.loading = true;
      try {
        const res = await fetch('/checkout', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({
            ...this.form,
            items: this.items.map(i => ({ id: i.id, qty: i.qty }))
          })
        });
        const data = await res.json();
        if (data.redirect) {
          this.items = [];
          this.open = false;
          this.showCheckout = false;
          window.open(data.redirect, '_blank');
        } else {
          this.checkoutError = data.error || 'Terjadi kesalahan.';
        }
      } catch (err) {
        this.checkoutError = 'Gagal terhubung ke server.';
      } finally {
        this.loading = false;
      }
    }
  };
}
