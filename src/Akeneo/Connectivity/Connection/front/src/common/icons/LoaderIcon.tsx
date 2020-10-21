import React, {SVGProps} from 'react';

export const LoaderIcon = (props: SVGProps<SVGSVGElement>) => (
    <svg xmlns='http://www.w3.org/2000/svg' width='24' height='24' viewBox='0 0 12 12' {...props}>
        <g fill='none' fillRule='evenodd'>
            <circle cx='6' cy='6' r='4' stroke='#A1A9B7' strokeWidth='2' />
            <path stroke='#FFF' strokeLinecap='round' d='M10 6a4 4 0 00-4-4'>
                <animateTransform
                    attributeName='transform'
                    attributeType='XML'
                    type='rotate'
                    dur='1s'
                    from='0 6 6'
                    to='360 6 6'
                    repeatCount='indefinite'
                />
            </path>
        </g>
    </svg>
);
