/**
 * StoreModel
 * Provides the client-side interface to the PHP/MySQL API.
 */
window.StoreModel = {
  keys: {
    user: 'savorly_user',
    cart: 'savorly_cart',
    orders: 'savorly_orders'
  },

  read(name, fallback = null) {
    try {
      const value = localStorage.getItem(this.keys[name] || name);
      return value === null ? fallback : JSON.parse(value);
    } catch {
      return fallback;
    }
  },

  write(name, value) {
    localStorage.setItem(this.keys[name] || name, JSON.stringify(value));
  },

  remove(name) {
    localStorage.removeItem(this.keys[name] || name);
  },

  async request(action, options = {}) {
    const response = await fetch(`app/controllers/api.php?action=${encodeURIComponent(action)}`, {
      credentials: 'same-origin',
      headers: { 'Content-Type': 'application/json', ...(options.headers || {}) },
      ...options
    });
    const data = await response.json();
    if (!response.ok || !data.ok) throw new Error(data.message || 'The request could not be completed.');
    return data;
  },

  bootstrap() {
    return this.request('bootstrap');
  },

  login(credentials) {
    return this.request('login', { method: 'POST', body: JSON.stringify(credentials) });
  },

  register(details) {
    return this.request('register', { method: 'POST', body: JSON.stringify(details) });
  },

  resendVerification(identifier) {
    return this.request('resend-verification', { method: 'POST', body: JSON.stringify({ identifier }) });
  },

  forgotPassword(identifier) {
    return this.request('forgot-password', { method: 'POST', body: JSON.stringify({ identifier }) });
  },

  updateProfile(details) {
    return this.request('profile', { method: 'POST', body: JSON.stringify(details) });
  },

  logout() {
    return this.request('logout', { method: 'POST', body: '{}' });
  },

  saveCart(items) {
    return this.request('cart', { method: 'POST', body: JSON.stringify({ items }) });
  },

  loadOrders() {
    return this.request('orders');
  },

  createOrder(details) {
    return this.request('order', { method: 'POST', body: JSON.stringify(details) });
  },

  cancelOrder(orderNumber) {
    return this.request('cancel-order', { method: 'POST', body: JSON.stringify({ orderNumber }) });
  }
};
