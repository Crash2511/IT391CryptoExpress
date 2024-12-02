import yfinance as yf
import mysql.connector
import time
import datetime

# MySQL database connection details
mydb = mysql.connector.connect(
  host="    ",
  user="    ",
  password="    ",
  database="   "
)

mycursor = mydb.cursor()

def fetch_and_insert(tickers):
  data = yf.download(tickers=tickers, period='1m', interval='1m')
  for index, row in data.iterrows():
    for ticker in tickers:
      sql = "INSERT INTO crypto_data (timestamp, ticker, price, change, percent_change) VALUES (%s, %s, %s, %s, %s)"
      val = (index, ticker, row[ticker]['Close'], row[ticker]['Change'], row[ticker]['% Change'])
      mycursor.execute(sql, val)
      mydb.commit()

tickers = ["BTC-USD", "ETH-USD", "SOL-USD", "DOGE-USD"]

while True:
  fetch_and_insert(tickers)
  time.sleep(60)
