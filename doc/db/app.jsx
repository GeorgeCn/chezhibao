import React from 'react'
import ReactDOM from 'react-dom'
import { hashHistory, Router, Route,Link } from 'react-router'

var SearchForm = React.createClass({
	getInitialState:function(){
		return {
			results:[],
		}
	},
	componentDidMount:function(){
		var that = this;
		document.addEventListener("click",function(){
			that.setState({
				show:false
			})
		})
	},
	searchAction:function(event){
		var input = event.target.value;
		var timer = null;
		var results = [];
		for(let i = 0,length = this.props.value.length;i<length;i++){
			if(this.props.value[i].indexOf(input) != -1){
				results.push(this.props.value[i])
			}
		}
		this.setState({
			results:results,
			show:true
		})
	},
	render(){
		return (
			<div className="sidebar-form">
				<input type="text" className="form-control" placeholder="Search..." onKeyUp={ this.searchAction }/>
				<ul className='search-results' style={{ display:this.state.show ? 'block' : 'none' }}>
					{
						this.state.results.map((item,index) => {
							return(
								<li key={ index }>
									<Link to={`/table/${ item }`}><i className="fa fa-table"></i>{ item }</Link>
								</li>
							)
						})
					}
				</ul>
			</div>
		)
	}
})

var NavSide = React.createClass ({
	getInitialState:function(){
		return { 
			current: null,
			tableArray:null
		}
	},
	componentDidMount:function(){
		var tableArray = [];
		for(var i=0;i<data.database.length;i++){
			for(var j=0;j<data.database[i].content.length;j++){
				tableArray = tableArray.concat(data.database[i].name+"_"+data.database[i].content[j].name)
			}
		}
		this.setState({
			tableArray:tableArray,
		})
	},
	showActive:function(index){
		return this.state.current == index ? 'treeview active' : 'treeview' ;
	},
	render(){
		let items = data.database.map((item,index) => {
			return (
				<li key={index} className={ this.showActive(index) } onClick={ () =>{ this.setState({ current:index })} } >
					<a href="javascript:;">
						<i className="fa fa-database"></i>
						<span>{ item.name }</span>
						<i className="fa fa-angle-left pull-right"></i>
					</a>
					<ul className="treeview-menu">
						{
							item.content.map((subitem,index) => {
								return (
									<li key={ index }>
										<Link to={`/table/${ item.name+"_"+subitem.name }`}>
											<i className="fa fa-table"></i>{ subitem.name }
										</Link>
									</li>
								)
							})
						}
					</ul>
				</li>
			)
		});
		return (
			<div>
				<aside className="main-sidebar">
					<SearchForm value={ this.state.tableArray }/>
					<nav className="sidebar">
						<ul className="sidebar-menu" >
							{ items }
						</ul>
					</nav>
				</aside>
				{ this.props.children }
			</div>
			
		)
	}
})

var ContentWrapper = React.createClass ({
	getInitialState:function(){
		return ({
			datatable:null,
			title:null
		})
	},
	componentWillReceiveProps:function(nextProps){
		this.changeId(nextProps.params.id);
	},
	componentDidMount:function(){
		this.changeId(this.props.params.id);
	},
	changeId:function(id){
		var targetTable,title;
		for(var i=0;i<data.database.length;i++){
			for(var j=0;j<data.database[i].content.length;j++){
				if(data.database[i].name + '_' + data.database[i].content[j].name == id ){
					title = data.database[i].name;
					targetTable = data.database[i].content[j];
				}
			}
		}
		this.setState({
			datatable:targetTable,
			title: title
		})
	},
	render(){
		if(this.state.datatable){
			return (
				<div className='content-wrapper'>
					<section className="content">
						<h3><i className="fa fa-database"></i> { this.state.title } / { this.state.datatable.name }</h3>
                        <p dangerouslySetInnerHTML={{__html: this.state.datatable.table.comment}}></p>
                        <p>
                            索引
                            {
                                this.state.datatable.table.indexes.map((item, index) => {
                                    return (
                                        <li key={index}>{item.name}:{item.columns.join(" ")}
                                        </li>
                                    )
                                })
                            }
                        </p>
						<table className='table table-hover table-condensed table-bordered'>
							<thead>
								<tr className='info'>
									{
										this.state.datatable.table.thead.map((item,index) => {
											return (
												<td key={ index }>{ item }</td>
											)
										})
									}
								</tr>
							</thead>
							<tbody>
								{
									this.state.datatable.table.tbody.map((item,index) => {
										return (
											<tr key={ index }>
												{
													item.map((item,index) => {
														if(item.indexOf('#') == -1){
															return (
																<td key={ index }>{ item }</td>
															)
														}else{
															return (
																<td key={ index } title={ item }>
																	<Link to={`/table/${ item.split('#')[1].replace('.','_') }`}>
																		{ item.split('#')[0] }
																	</Link>
																</td>
															)
														}
													})
												}
											</tr>
										)
									})
								}
							</tbody>
						</table>
					</section>
				</div>
			)
		}else{
			return false
		}
	}
})

// 定义页面上的路由
var routes = (  
	<Router history={ hashHistory }>
		<Route path="/" component={ NavSide } >
			<Route path="table/:id" component={ ContentWrapper } />
		</Route>
	</Router>
);
ReactDOM.render(routes,document.getElementById('wrapper'));