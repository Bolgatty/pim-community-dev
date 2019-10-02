import * as React from 'react';
import {useContext} from 'react';

import {TranslateContext} from '../../context/translate-context';

interface Props {
  id: string;
  placeholders?: any;
  count?: number;
}

export const Translate = ({id, placeholders = {}, count = 1}: Props) => {
  const translate = useContext(TranslateContext);

  return <>{translate(id, placeholders, count)}</>;
};
