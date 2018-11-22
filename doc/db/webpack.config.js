module.exports = {
	entry:{
		app: './app.jsx',
	},
  output: {
    path: __dirname, // This is where images AND js will go
    //publicPath: 'http://mycdn.com/', // This is used to generate URLs to e.g. images
    filename: '[name].js'
  },
  module: {
    loaders: [
      { test: /\.coffee$/, loader: 'coffee-loader' },
      {test: /\.js$/,loader: 'babel-loader'}, 
      {test: /\.jsx$/,exclude: /(node_modules|bower_components)/,loader: 'babel-loader',query: {presets: ['react', 'es2015']}},
      { test: /\.less$/, loader: 'style-loader!css-loader!less-loader' }, // use ! to chain loaders
      { test: /\.css$/, loader: 'style-loader!css-loader' },
      { test: /\.(png|jpg)$/, loader: 'url-loader?limit=8192' } // inline base64 URLs for <=8k images, direct URLs for the rest
    ]
  },
  resolve: {
    // you can now require('file') instead of require('file.coffee')
    extensions: ['', '.js', '.json', '.coffee'] 
  }
};