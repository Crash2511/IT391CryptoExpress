import yfinance as yf
import mysql.connector
import pandas as pd

mydb = mysql.connector.connect(
  host="    ",
  user="     ",
  password="  ",
  database="    "
)

mycursor = mydb.cursor()
tickers = ["BTC-USD", "ETH-USD", "SOL-USD", "DOGE-USD"]

data = yf.download(tickers)

data = data.reset_index()
data['timestamp'] = data['Date'].astype(str)
data = data[['timestamp', 'Open', 'High', 'Low', 'Close', 'Adj Close', 'Volume']]

for index, row in data.iterrows():
    sql = "INSERT INTO crypto_data (timestamp, Open, High, Low, Close, Adj_Close, Volume) VALUES (%s, %s, %s, %s, %s, %s, %s)"
    val = (row['timestamp'], row['Open'], row['High'], row['Low'], row['Close'], row['Adj Close'], row['Volume'])
    mycursor.execute(sql, val)

mydb.commit()
print("Data inserted successfully!")

mycursor.close()
mydb.close()
