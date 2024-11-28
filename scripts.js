document.addEventListener("DOMContentLoaded", () => {
    const cryptos = [
        { name: "Bitcoin", symbol: "BTC", price: 64000 },
        { name: "Ethereum", symbol: "ETH", price: 2400 },
        { name: "Litecoin", symbol: "LTC", price: 64 }
    ];

    const portfolio = {
        BTC: 0,
        ETH: 0,
        LTC: 0
    };

    function updateMarketOverview() {
        const cryptoList = document.getElementById("crypto-list");
        cryptoList.innerHTML = "";

        cryptos.forEach(crypto => {
            const item = document.createElement("div");
            item.className = "crypto-item";
            item.innerHTML = `
                <h3>${crypto.name} (${crypto.symbol})</h3>
                <p>Price: $${crypto.price.toLocaleString()}</p>
            `;
            cryptoList.appendChild(item);
        });
    }

    function updatePortfolio() {
        const portfolioList = document.getElementById("portfolio-list");
        portfolioList.innerHTML = "";

        Object.keys(portfolio).forEach(symbol => {
            const item = document.createElement("div");
            item.className = "portfolio-item";
            item.innerHTML = `
                <h3>${symbol}</h3>
                <p>Amount: ${portfolio[symbol]}</p>
            `;
            portfolioList.appendChild(item);
        });
    }

    function populateCryptoSelect() {
        const select = document.getElementById("crypto");
        select.innerHTML = "";

        cryptos.forEach(crypto => {
            const option = document.createElement("option");
            option.value = crypto.symbol;
            option.textContent = `${crypto.name} (${crypto.symbol})`;
            select.appendChild(option);
        });
    }

    function tradeCrypto(action) {
        const crypto = document.getElementById("crypto").value;
        const amount = parseInt(document.getElementById("amount").value);

        if (!amount || amount <= 0) {
            alert("Please enter a valid amount.");
            return;
        }

        if (action === "buy") {
            portfolio[crypto] += amount;
        } else if (action === "sell") {
            if (portfolio[crypto] >= amount) {
                portfolio[crypto] -= amount;
            } else {
                alert("Not enough balance to sell.");
                return;
            }
        }

        updatePortfolio();
    }

    document.getElementById("buy-btn").addEventListener("click", () => {
        tradeCrypto("buy");
    });

    document.getElementById("sell-btn").addEventListener("click", () => {
        tradeCrypto("sell");
    });

    updateMarketOverview();
    updatePortfolio();
    populateCryptoSelect();
});

