import React, { Component } from 'react';
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome'
import ReactSpeedometer from 'react-d3-speedometer'
import { NavLink, Link } from 'react-router-dom'

import Chart from '../layouts/Chart'
import Loader from '../layouts/Loader';
import { chartData } from '../../resources/ChartHelper'

export default class PanelAnalysis extends Component {
	constructor(props) {
        super(props)
        this.state = {
            chart: { datasets:[], labels:[], filter:'today'},
            panels: [],
            activePanel:'',
            filter: this.props.match.params.number,
            stats: '',
        }
        this.handleFilterValue = this.handleFilterValue.bind(this)
        this.fetchData = this.fetchData.bind(this)
    }

	// Get data when the component loads
    componentDidMount(){
    	this.fetchData()      	
    }

	// Tear down the interval 
    componentWillUnmount() {
	    //clearInterval(this.timerID);
	}	

	componentWillReceiveProps(nextProps) {
		this.setState({
			filter:nextProps.match.params.number,
			loader:true,
		},()=>{
			this.fetchData()					
			this.setState({loader:false})
		})
    }

	fetchData(){
		this.setState({
			loader:true
		}, ()=>{
			axios.post('api/customers/panel-analysis', {
	      		chart_filter: this.state.chart.filter,	
	      		panel: this.state.filter,	
	    	})
	    	.then((response) => {
	    		var chart = chartData(response.data.chart.data, ['temperature', 'energy', 'intensity', 'humidity'])
	    		chart.filter = this.state.chart.filter
	    		this.setState({
					loader:false,
					chart: chart,
	    			panels: response.data.panels.map(function(e) { return e.id}),
	    			stats: response.data.stats,
	    			activePanel: response.data.activePanel,
				})
	    	})
	    	.catch((error) => {
	    		if(User.hasTokenHasExpired(error.response.data)){
	    			this.props.history.push('/login')
	    		}
	    	})			
		})
	}

	handleFilterValue(value){
		this.setState({
			chart:{
				datasets:this.state.chart.datasets, 
				labels:this.state.chart.labels, 
				filter: value 
			}
		}, ()=>{
			this.fetchData()
		})
	}

    render() {
    	const panels = this.state.panels.map((panel)=>(
    		<div key={panel} className="row">
				<NavLink className="col-12 py-2 px-0 border-white border-bottom text-white" to={`/panel-analysis/panels/${panel}`}>
					<FontAwesomeIcon icon="qrcode" size="lg" className="mr-2" />
					Panel #{panel}
				</NavLink>
			</div>	
    	))

    	return (
			<div id="carbon-reports" className="row m-0">
				<Loader load={this.state.loader} /> 
				<div className="col-2 bg-dark-primary panel-nav-bar px-0" style={{ boxShadow: '1px 2px 2px rgba(0, 0, 0, 0.7)' }}>	
					<div style={{height:'450px', overflowY:'auto', overflowX:'hidden'}} className="bg-dark-primary">
						<div className="row">
							<NavLink className="col-12 py-2 px-0 border-white border-bottom text-white" to={`/panel-analysis/panels/all`}>
								<FontAwesomeIcon icon="qrcode" size="lg" className="mr-2" />
								All Panels
							</NavLink>
						</div>					
						{panels}					
					</div>
				</div>
				<div className="col-10 p-0 pl-2">
					<div className="row mx-0">
						<div className="col-12 card-shadow">
							<div className="row">
								<div className="col-3 py-2 text-white bg-dark-secondary border border-white">
									{ this.state.filter == 'all' ? (<span>Panels: {this.state.panels.length} </span>) : (<span>Serial NO: {Math.floor((Math.random() * 10000000) + 1)}</span>)}
								</div>
								<div className="col-3 py-2 text-white bg-dark-secondary border border-white">
									{ this.state.filter == 'all' ? (<span></span>) : (<span>Voltage : {this.state.activePanel.voltage}V</span>)}
								</div>
								<div className="col-3 py-2 text-white bg-dark-secondary border border-white">
									{ this.state.filter == 'all' ? (<span></span>) : (<span>Power : {this.state.activePanel.power}w</span>)}
								</div>
								<div className="col-3 py-2 text-white bg-dark-secondary border border-white">
									{ this.state.filter == 'all' ? (<span></span>) : (<span>Active From : {(new Date(this.state.activePanel.created_at)).toLocaleDateString('en-GB')}</span>)}
								</div>
							</div>
						</div>
					</div>
					<div className="row mx-0 mt-1">
						<div className="col-12 card-shadow pb-3">
							<Chart
								data={ this.state.chart }
								width={ 100 }
								height={ 287 }
								handleFilterValue={this.handleFilterValue}
								options={{
									maintainAspectRatio: false
								}}
							/>
						</div>
					</div>
				</div>
				<div className="col-12 mt-1 bg-dark-secondary text-white card-shadow">
					<div className="row">
						<div className="col-12 col-lg-2 text-center border py-4">
							<div className="w-100" style={{
							    height: '100px'
							}}>
								<ReactSpeedometer
									fluidWidth={true}
									maxValue={60}
								  	value={parseInt(this.state.stats.temperature)}
								  	width={200}
								  	height={250}
								  	needleColor="rgb(220, 53, 69)"
								  	segments={5}
								  	textColor="#fff"
								  	needleTransitionDuration={4000}
								  	needleTransition="easeElastic"
								  	currentValueText={String(this.state.stats.temperature)}
								  	ringWidth={40}
								/>
							</div>
							<div className="col-12">Temperature</div>
						</div>
						<div className="col-12 col-lg-2 text-center border py-4">
							<div className="w-100" style={{
							    height: '100px'
							}}>
								<ReactSpeedometer
									fluidWidth={true}
									maxValue={100}
								  	value={parseInt(this.state.stats.humidity)}
								  	width={200}
								  	height={250}
								  	needleColor="rgb(23, 162, 184)"
								  	segments={5}
								  	textColor="#fff"
								  	needleTransitionDuration={4000}
								  	needleTransition="easeElastic"
								  	currentValueText={String(this.state.stats.humidity)}
								  	ringWidth={40}
								/>
							</div>
							<div className="col-12">Humidity</div>
						</div>
						<div className="col-12 col-lg-2 text-center border py-4">
							<div className="w-100" style={{
							    height: '100px'
							}}>
								<ReactSpeedometer
									fluidWidth={true}
									maxValue={100}
								  	value={parseInt(this.state.stats.intensity)}
								  	width={200}
								  	height={250}
								  	needleColor="rgb(255, 193, 7)"
								  	segments={5}
								  	textColor="#fff"
								  	needleTransitionDuration={4000}
								  	needleTransition="easeElastic"
								  	currentValueText={String(this.state.stats.intensity)}
								  	ringWidth={40}
								/>
							</div>
							<div className="col-12">Intensity</div>
						</div>
						<div className="col-12 col-lg-2 text-center border py-4">
							<div className="w-100" style={{
							    height: '100px'
							}}>
								<ReactSpeedometer
									fluidWidth={true}
									maxValue={50}
								  	value={parseInt(this.state.stats.energy)}
								  	width={200}
								  	height={250}
								  	needleColor="purple"
								  	segments={5}
								  	textColor="#fff"
								  	needleTransitionDuration={4000}
								  	needleTransition="easeElastic"
								  	currentValueText={String(this.state.stats.energy)}
								  	ringWidth={40}
								/>
							</div>
							<div className="col-12">Energy</div>
						</div>
						<div className="col-12 col-lg-2 text-center border py-4">
							<div className="w-100" style={{
							    height: '100px'
							}}>
								<ReactSpeedometer
									fluidWidth={true}
									maxValue={5}
								  	value={parseInt(this.state.stats.credits)}
								  	width={200}
								  	height={250}
								  	needleColor="green"
								  	segments={5}
								  	textColor="#fff"
								  	needleTransitionDuration={4000}
								  	needleTransition="easeElastic"
								  	currentValueText={String(this.state.stats.credits)}
								  	ringWidth={40}
								/>
							</div>
							<div className="col-12">Credits</div>
						</div>
						<div className="col-12 col-lg-2 text-center border py-4">
							<div className="w-100" style={{
							    height: '100px'
							}}>
								<ReactSpeedometer
									fluidWidth={true}
									maxValue={200}
								  	value={parseInt(this.state.stats.amount)}
								  	width={200}
								  	height={250}
								  	needleColor="blue"
								  	segments={5}
								  	textColor="#fff"
								  	needleTransitionDuration={4000}
								  	needleTransition="easeElastic"
								  	currentValueText={String(this.state.stats.amount)}
								  	ringWidth={40}
								/>
							</div>
							<div className="col-12">Value</div>
						</div>
					</div>
				</div>
		    </div>
    	)
    }
}