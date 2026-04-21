import { useState, useEffect } from "react";
import "./App.css";

function App() {
  const [products, setProducts] = useState([]);
  const [cart, setCart] = useState([]);

  useEffect(() => {
    fetchProducts();
  }, []);

  const fetchProducts = async () => {
    try {
        const response = await fetch("http://localhost:8001/api"); 
        
        if (!response.ok) throw new Error("Ошибка сети");
        
        const data = await response.json();
        setProducts(data); // Записываем данные в состояние
    } catch (e) {
        console.error("Ошибка при загрузке:", e);
    }
};

  const addToCart = (p) => setCart([...cart, p]);
  const total = cart.reduce((sum, item) => sum + item.price, 0);

  return (
    <div className="app-container">
      <aside className="sidebar">
        <h2>Rysgally Hasap Market</h2>
        <div className="stats">Jemi: {total} TMT</div>
        <button className="pay-button" onClick={() => alert('Töleg kabul edildi!')}>Töleg Et</button>
        <div className="cart-list">
          {cart.map((item, i) => (
            <div key={i} className="cart-item">{item.name} - {item.price} TMT</div>
          ))}
        </div>
      </aside>
      
      <main className="product-grid">
        {products.length > 0 ? (
          products.map((p) => (
            <div key={p.id} className="product-card" onClick={() => addToCart(p)}>
              <h3>{p.name}</h3>
              <p>{p.price} TMT</p>
            </div>
          ))
        ) : (
          <div className="no-products">Harytlar ýüklenýär ýa-da servere baglanmady...</div>
        )}
      </main>
    </div>
  );
}

export default App;