import ReactDOM from 'react-dom';
import React from "react";
import {
  Dashboard,
  DashboardHelper,
  DATA_QUALITY_INSIGHTS_DASHBOARD_CHANGE_TIME_PERIOD,
  DATA_QUALITY_INSIGHTS_DASHBOARD_FILTER_FAMILY,
  DATA_QUALITY_INSIGHTS_DASHBOARD_FILTER_CATEGORY
} from '@akeneo-pim-community/data-quality-insights/src/index';

interface SectionConfig {
  align: string;
}
interface LocaleEvent {
  localeCode: string;
}
interface ScopeEvent {
  scopeCode: string;
}

const UserContext = require('pim/user-context');
const BaseDashboard = require('akeneo/data-quality-insights/view/dqi-dashboard/base-dashboard');

class SectionView extends BaseDashboard {

  render() {
    const catalogLocale: string = UserContext.get('catalogLocale');
    const catalogChannel: string = UserContext.get('catalogScope');

    ReactDOM.render(
      <div>
        <DashboardHelper />
        <Dashboard
          timePeriod={this.timePeriod}
          catalogLocale={catalogLocale}
          catalogChannel={catalogChannel}
          familyCode={this.familyCode}
          categoryCode={this.categoryCode}
          axes={this.axes}
        />
      </div>,
      this.el
    );
  }
}

export = SectionView;
