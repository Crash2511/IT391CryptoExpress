import requests
import pandas
import sqlalchemy
apikey = '0abd643d-332d-4d9d-8ee0-87cd0c97289d'


headers = {
  'X-CMC_PRO_API_KEY' = apikey.key,
  'Accepts' = 'application/json',
}

params = {
  'start' : 1,
  'limit' : '5000',
  'convert' : 'USD;
}

url = 'https://pro-api.coinmarketcap.com/v1/cryptocurrency/listings/latest'

json = requests.get(url, params=params, headers=headers).json

crypto = json['data']

for x in crypto:
  print(x['symbol'],x['quote']['USD']['price'])


engine = sqlalchemy.create_engine("mysql+pymysql://scott:tiger@localhost/foo")  #connection between python code and sql server
crypto.to_sql(name='table_name', con=conn, if_exists='replace', index=False) #two sql method to push the data to the table

#engine.close()
