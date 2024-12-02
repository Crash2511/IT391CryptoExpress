import yfinance as yf
import pymysql  # Using pymysql for MySQL connection
import time
import datetime

# MySQL database connection details
mydb = pymysql.connect(
  host="localhost",  # Your MySQL host
  user="root",  # Your MySQL username
  password="your_password",  # Your MySQL password
  database="crypto_express"  # Your database name
)

mycursor = mydb.cursor()

def fetch_and_insert(tickers):
    data = yf.download(tickers=tickers, period='1m', interval='1m')
    
    for index, row in data.iterrows():
        for ticker in tickers:
            # Extract relevant information from yfinance data
            price = row['Close']
            price_change = row['Close'] - row['Open']  # Price change from open to close
            change_percent = (price_change / row['Open']) * 100 if row['Open'] != 0 else 0
            market_cap = row['Volume'] * price  # Simplified market cap estimate
            volume = row['Volume']
            circulating_supply = 0  # Placeholder, need to fetch from another source or API
            total_supply = 0  # Placeholder, need to fetch from another source or API
            price_high = row['High']
            price_low = row['Low']

            # Insert data into crypto_information table
            sql = """
            INSERT INTO crypto_information 
            (name, name_abreviation, price, price_change, change_percent, market_cap, volume, circulating_supply, total_supply, price_high, price_low, trade_time)
            VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, CURRENT_TIMESTAMP)
            ON DUPLICATE KEY UPDATE 
            price = VALUES(price), 
            price_change = VALUES(price_change), 
            change_percent = VALUES(change_percent),
            market_cap = VALUES(market_cap),
            volume = VALUES(volume),
            circulating_supply = VALUES(circulating_supply),
            total_supply = VALUES(total_supply),
            price_high = VALUES(price_high),
            price_low = VALUES(price_low),
            trade_time = CURRENT_TIMESTAMP
            """
            # Assuming `name` and `name_abreviation` are ticker and abbreviation
            name_abreviation = ticker.split('-')[0]  # Example: "BTC-USD" => "BTC"
            val = (ticker, name_abreviation, price, price_change, change_percent, market_cap, volume, circulating_supply, total_supply, price_high, price_low)

            # Execute the SQL query and commit the transaction
            mycursor.execute(sql, val)
            mydb.commit()

tickers = ["BTC-USD", "ETH-USD", "SOL-USD", "DOGE-USD"]

while True:
    fetch_and_insert(tickers)
    time.sleep(60)
