import { useState, useEffect } from "react";
import "./App.css";

function App() {
  const [products, setProducts] = useState([]);
  const [cart, setCart] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);
  const [retryCount, setRetryCount] = useState(0);

  useEffect(() => {
    fetchProducts();
  }, [retryCount]);

  const fetchProducts = async () => {
    setLoading(true);
    setError(null);
    
    try {
      const controller = new AbortController();
      const timeoutId = setTimeout(() => controller.abort(), 10000); // 10s timeout
      
      const response = await fetch("http://localhost:8001/api", {
        signal: controller.signal,
        headers: { "Accept": "application/json" }
      });
      
      clearTimeout(timeoutId);
      
      if (!response.ok) {
        throw new Error(`HTTP ${response.status}: ${response.statusText}`);
      }
      
      const data = await response.json();
      
      if (!Array.isArray(data)) {
        throw new Error("Unexpected response format");
      }
      
      setProducts(data);
    } catch (e) {
      console.error("Ýükleme säwligi:", e);
      
      if (e.name === "AbortError") {
        setError("Server jogaplandyrmady. Biraz g'ammatdan soň täzeden synap goring.");
      } else {
        setError(e.message || "Nädogry ýalnyşlyk");
      }
    } finally {
      setLoading(false);
    }
  };

  const handleRetry = () => {
    setRetryCount(prev => prev + 1);
  };

  const addToCart = (p) => {
    setCart([...cart, { ...p, cartId: Date.now() + Math.random() }]);
  };

  const removeFromCart = (cartId) => {
    setCart(cart.filter(item => item.cartId !== cartId));
  };

  const total = cart.reduce((sum, item) => sum + (item.price || 0), 0);

  return (
    <div className="app-container">
      <aside className="sidebar">
        <h2>Rysgally Hasap Market</h2>
        <div className="stats">Jemi: {total.toFixed(2)} TMT</div>
        <button className="pay-button" onClick={() => {
          if (cart.length === 0) {
            alert('Sebetde haryt ýok!');
            return;
          }
          alert(`Söwda tamamlady: ${total.toFixed(2)} TMT`);
          setCart([]);
        }}>
          Töleg Et
        </button>
        <div className="cart-list">
          {cart.length === 0 ? (
            <div style={{ padding: "10px", color: "#999" }}>Sebetde haryt ýok</div>
          ) : (
            cart.map((item) => (
              <div key={item.cartId} className="cart-item">
                <span>{item.name} - {item.price} TMT</span>
                <button 
                  onClick={() => removeFromCart(item.cartId)}
                  style={{
                    background: "none",
                    border: "none",
                    color: "red",
                    cursor: "pointer",
                    marginLeft: "5px"
                  }}
                >
                  ✕
                </button>
              </div>
            ))
          )}
        </div>
      </aside>
      
      <main className="product-grid">
        {loading ? (
          <div className="no-products">
            <div style={{ fontSize: "18px", marginBottom: "10px" }}>⏳ Harytlar ýüklenýär...</div>
            <div style={{ fontSize: "12px", color: "#999" }}>Biraz gözläň...</div>
          </div>
        ) : error ? (
          <div className="no-products" style={{ backgroundColor: "#fff3cd" }}>
            <div style={{ fontSize: "16px", color: "#856404", marginBottom: "10px" }}>⚠️ {error}</div>
            <button 
              onClick={handleRetry}
              style={{
                padding: "8px 16px",
                backgroundColor: "#856404",
                color: "white",
                border: "none",
                borderRadius: "4px",
                cursor: "pointer"
              }}
            >
              Täzeden synap goring
            </button>
          </div>
        ) : products.length > 0 ? (
          products.map((p) => (
            <div key={p.id} className="product-card" onClick={() => addToCart(p)}>
              <h3>{p.name}</h3>
              <p>{p.price} TMT</p>
            </div>
          ))
        ) : (
          <div className="no-products">Harytlar tapylmady</div>
        )}
      </main>
    </div>
  );
}

export default App;